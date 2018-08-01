<?php

namespace Shopping\ApiFilterBundle\EventListener;

use Shopping\ApiFilterBundle\Annotation AS Api;
use Shopping\ApiFilterBundle\Exception\FilterException;
use Shopping\ApiFilterBundle\Exception\SortException;
use Shopping\ApiFilterBundle\Service\ApiService;
use Doctrine\Common\Annotations\Reader;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;

/**
 * Class AnnotationListener
 *
 * Reads the filter/sort/pagination annotations and stores them in the ApiService.
 *
 * @package Shopping\ApiFilterBundle\EventListener
 */
class AnnotationListener
{
    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var Reader
     */
    private $reader;

    /**
     * @var bool
     */
    private $masterRequest = true;

    /**
     * @var ApiService
     */
    private $apiService;

    /**
     * AllowedFilterAnnotationListener constructor.
     * @param Reader $reader
     * @param RequestStack $requestStack
     * @param ApiService $apiService
     */
    public function __construct(Reader $reader, RequestStack $requestStack, ApiService $apiService)
    {
        $this->requestStack = $requestStack;
        $this->reader = $reader;
        $this->apiService = $apiService;
    }

    /**
     * @param FilterControllerEvent $event
     * @throws FilterException
     * @throws SortException
     */
    public function onKernelController(FilterControllerEvent $event)
    {
        //Only parse annotations on original action
        if (!$this->masterRequest) {
            return;
        }
        $this->masterRequest = false;

        $controller = $event->getController();

        if (!is_array($controller)) {
            return;
        }

        $methodAnnotations = $this->getAnnotationsByController($controller);

        //Filters
        $filters = array_filter($methodAnnotations, function($annotation) { return $annotation instanceof Api\Filter; });
        $this->apiService->handleAllowedFilters($filters);

        //Sorts
        $sorts = array_filter($methodAnnotations, function($annotation) { return $annotation instanceof Api\Sort; });
        $this->apiService->handleAllowedSorts($sorts);

        //Pagination
        /** @var Api\Pagination[] $paginations */
        $paginations = array_filter($methodAnnotations, function($annotation) { return $annotation instanceof Api\Pagination; });
        if (count($paginations) > 0) {
            $this->apiService->handleIsPaginatable(reset($paginations));
        }
    }

    /**
     * @param array $controller
     * @return array
     */
    private function getAnnotationsByController(array $controller): array
    {
        /** @var Controller $controllerObject */
        list($controllerObject, $methodName) = $controller;

        $controllerReflectionObject = new \ReflectionObject($controllerObject);
        $reflectionMethod = $controllerReflectionObject->getMethod($methodName);

        return $this->reader->getMethodAnnotations($reflectionMethod);
    }
}