<?php

namespace Shopping\ApiFilterBundle\EventListener;

use Shopping\ApiFilterBundle\Annotation AS Rfc14;
use Shopping\ApiFilterBundle\Exception\FilterException;
use Shopping\ApiFilterBundle\Exception\SortException;
use Shopping\ApiFilterBundle\Service\Rfc14Service;
use Doctrine\Common\Annotations\Reader;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;

/**
 * Class AnnotationListener
 *
 * Reads the filter/sort/pagination annotations and stores them in the Rfc14Service.
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
     * @var Rfc14Service
     */
    private $rfc14Service;

    /**
     * AllowedFilterAnnotationListener constructor.
     * @param Reader $reader
     * @param RequestStack $requestStack
     * @param Rfc14Service $rfc14Service
     */
    public function __construct(Reader $reader, RequestStack $requestStack, Rfc14Service $rfc14Service)
    {
        $this->requestStack = $requestStack;
        $this->reader = $reader;
        $this->rfc14Service = $rfc14Service;
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
        $filters = array_filter($methodAnnotations, function($annotation) { return $annotation instanceof Rfc14\Filter; });
        $this->rfc14Service->handleAllowedFilters($filters);

        //Sorts
        $sorts = array_filter($methodAnnotations, function($annotation) { return $annotation instanceof Rfc14\Sort; });
        $this->rfc14Service->handleAllowedSorts($sorts);

        //Pagination
        /** @var Rfc14\Pagination[] $paginations */
        $paginations = array_filter($methodAnnotations, function($annotation) { return $annotation instanceof Rfc14\Pagination; });
        if (count($paginations) > 0) {
            $this->rfc14Service->handleIsPaginatable(reset($paginations));
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