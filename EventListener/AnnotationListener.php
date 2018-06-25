<?php

namespace Ofeige\Rfc14Bundle\EventListener;

use Ofeige\Rfc14Bundle\Annotation AS Rfc14;
use Ofeige\Rfc14Bundle\Service\Filter;
use Ofeige\Rfc14Bundle\Service\Pagination;
use Ofeige\Rfc14Bundle\Service\Sort;
use Doctrine\Common\Annotations\Reader;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;

class AnnotationListener
{
    /**
     * @var RequestStack
     */
    private $requestStack;
    /**
     * @var Filter
     */
    private $filter;
    /**
     * @var Reader
     */
    private $reader;
    /**
     * @var Sort
     */
    private $sort;
    /**
     * @var Pagination
     */
    private $pagination;

    /**
     * @var bool
     */
    private $masterRequest = true;

    /**
     * AllowedFilterAnnotationListener constructor.
     * @param Reader $reader
     * @param RequestStack $requestStack
     * @param Filter $filter
     * @param Sort $sort
     * @param Pagination $pagination
     */
    public function __construct(Reader $reader, RequestStack $requestStack, Filter $filter, Sort $sort, Pagination $pagination)
    {
        $this->requestStack = $requestStack;
        $this->filter = $filter;
        $this->reader = $reader;
        $this->sort = $sort;
        $this->pagination = $pagination;
    }

    /**
     * @param FilterControllerEvent $event
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
        $this->filter->handleAllowed($filters);

        //Sorts
        $sorts = array_filter($methodAnnotations, function($annotation) { return $annotation instanceof Rfc14\Sort; });
        $this->sort->handleAllowed($sorts);

        //Pagination
        /** @var Rfc14\Pagination[] $paginations */
        $paginations = array_filter($methodAnnotations, function($annotation) { return $annotation instanceof Rfc14\Pagination; });
        if (count($paginations) > 0) {
            $this->pagination->handleIsPaginatable(reset($paginations));
        }
    }

    private function getAnnotationsByController(array $controller): array
    {
        /** @var Controller $controllerObject */
        list($controllerObject, $methodName) = $controller;

        $controllerReflectionObject = new \ReflectionObject($controllerObject);
        $reflectionMethod = $controllerReflectionObject->getMethod($methodName);

        return $this->reader->getMethodAnnotations($reflectionMethod);
    }
}