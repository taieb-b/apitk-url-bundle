<?php

declare(strict_types=1);

namespace Shopping\ApiTKUrlBundle\EventListener;

use Doctrine\Common\Annotations\Reader;
use ReflectionException;
use ReflectionObject;
use Shopping\ApiTKUrlBundle\Annotation as Api;
use Shopping\ApiTKUrlBundle\Exception\FilterException;
use Shopping\ApiTKUrlBundle\Exception\SortException;
use Shopping\ApiTKUrlBundle\Service\ApiService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Event\ControllerEvent;

/**
 * Class AnnotationListener.
 *
 * Reads the filter/sort/pagination annotations and stores them in the ApiService.
 *
 * @package Shopping\ApiTKUrlBundle\EventListener
 */
class AnnotationListener
{
    /**
     * @var Reader
     */
    private $reader;

    /**
     * @var bool
     */
    private $mainRequest = true;

    /**
     * @var ApiService
     */
    private $apiService;

    /**
     * AllowedFilterAnnotationListener constructor.
     *
     * @param Reader     $reader
     * @param ApiService $apiService
     */
    public function __construct(Reader $reader, ApiService $apiService)
    {
        $this->reader = $reader;
        $this->apiService = $apiService;
    }

    /**
     * @param ControllerEvent $event
     *
     * @throws FilterException
     * @throws SortException
     * @throws ReflectionException
     */
    public function onKernelController(ControllerEvent $event): void
    {
        // Only parse annotations on original action
        if (!$this->mainRequest) {
            return;
        }
        $this->mainRequest = false;

        $controller = $event->getController();

        if (!is_array($controller)) {
            return;
        }

        $methodAnnotations = $this->getAnnotationsByController($controller);

        // Filters
        $filters = array_filter($methodAnnotations, static function ($annotation) { return $annotation instanceof Api\Filter; });
        $this->apiService->handleAllowedFilters($filters);

        // Sorts
        $sorts = array_filter($methodAnnotations, static function ($annotation) { return $annotation instanceof Api\Sort; });
        $this->apiService->handleAllowedSorts($sorts);

        // Pagination
        /** @var Api\Pagination[] $paginations */
        $paginations = array_filter($methodAnnotations, static function ($annotation) { return $annotation instanceof Api\Pagination; });
        if (count($paginations) > 0) {
            $pagination = reset($paginations);
            if ($pagination !== false) {
                $this->apiService->handleIsPaginatable($pagination);
            }
        }
    }

    /**
     * @param array $controller
     *
     * @throws ReflectionException
     *
     * @return array
     */
    private function getAnnotationsByController(array $controller): array
    {
        /** @var AbstractController $controllerObject */
        list($controllerObject, $methodName) = $controller;

        $controllerReflectionObject = new ReflectionObject($controllerObject);
        $reflectionMethod = $controllerReflectionObject->getMethod($methodName);

        return $this->reader->getMethodAnnotations($reflectionMethod);
    }
}
