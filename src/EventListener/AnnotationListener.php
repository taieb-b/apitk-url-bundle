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
 * Reads the filter/sort/pagination annotations and stores them in the ApiService.
 */
class AnnotationListener
{
    private bool $mainRequest = true;

    public function __construct(
        private Reader $reader,
        private ApiService $apiService
    ) {
    }

    /**
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
     * @throws ReflectionException
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
