<?php

namespace App\Service;

use Doctrine\Common\Annotations\Reader;
use EXSyst\Component\Swagger\Operation;
use EXSyst\Component\Swagger\Parameter;
use EXSyst\Component\Swagger\Swagger;
use Nelmio\ApiDocBundle\Describer\DescriberInterface;
use Nelmio\ApiDocBundle\Util\ControllerReflector;
use Symfony\Component\Routing\RouteCollection;
use Ofeige\Rfc14Bundle\Annotation AS Rfc14;

class AnnotationDescriber implements DescriberInterface
{
    /**
     * @var RouteCollection
     */
    private $routeCollection;
    /**
     * @var ControllerReflector
     */
    private $controllerReflector;
    /**
     * @var Reader
     */
    private $reader;

    //TODO: Replace ControllerReflector with something we can depend on
    public function __construct(RouteCollection $routeCollection, ControllerReflector $controllerReflector, Reader $reader)
    {
        $this->routeCollection = $routeCollection;
        $this->controllerReflector = $controllerReflector;
        $this->reader = $reader;
    }

    public function describe(Swagger $api)
    {
        $paths = $api->getPaths();
        foreach ($paths as $uri => $path) {
            foreach ($path->getMethods() as $method) {
                /** @var Operation $operation */
                $operation = $path->getOperation($method);

                foreach ($this->getMethodsToParse() as $method => list($methodPath, $httpMethods)) {
                    if ($methodPath === $uri) {
                        $methodAnnotations = $this->reader->getMethodAnnotations($method);

                        /** @var Rfc14\Filter[] $filters */
                        $filters = array_filter($methodAnnotations, function($annotation) { return $annotation instanceof Rfc14\Filter; });
                        $this->addFiltersToOperation($operation, $filters);

                        /** @var Rfc14\Sort[] $sorts */
                        $sorts = array_filter($methodAnnotations, function($annotation) { return $annotation instanceof Rfc14\Sort; });
                        $this->addSortsToOperation($operation, $sorts);


                        //Pagination
                        /** @var Rfc14\Pagination[] $paginations */
                        $paginations = array_filter($methodAnnotations, function($annotation) { return $annotation instanceof Rfc14\Pagination; });
                        $this->addPaginationsToOperation($operation, $paginations);
                    }
                }

                // Default Response
                if (0 === iterator_count($operation->getResponses())) {
                    $defaultResponse = $operation->getResponses()->get('default');
                    $defaultResponse->setDescription('foobar');
                }
            }
        }
    }

    private function getMethodsToParse(): \Generator
    {
        foreach ($this->routeCollection->all() as $route) {
            if (!$route->hasDefault('_controller')) {
                continue;
            }

            $controller = $route->getDefault('_controller');
            if ($callable = $this->controllerReflector->getReflectionClassAndMethod($controller)) {
                $path = $this->normalizePath($route->getPath());
                $httpMethods = $route->getMethods() ?: Swagger::$METHODS;
                $httpMethods = array_map('strtolower', $httpMethods);
                $supportedHttpMethods = array_intersect($httpMethods, Swagger::$METHODS);

                if (empty($supportedHttpMethods)) {
                    continue;
                }

                yield $callable[1] => [$path, $supportedHttpMethods];
            }
        }
    }

    private function normalizePath(string $path): string
    {
        if ('.{_format}' === substr($path, -10)) {
            $path = substr($path, 0, -10);
        }

        return $path;
    }

    /**
     * @param Operation $operation
     * @param Rfc14\Filter[] $filters
     */
    private function addFiltersToOperation(Operation $operation, array $filters): void
    {
        foreach ($filters as $filter) {
            $parameter = new Parameter([
                'name' => 'filter[' . $filter->name . '][' . $filter->allowedComparisons[0] . ']',
                'in' => 'query',
                'type' => 'string',
                'required' => false,
                'description' => 'Only show entries, which match this ' . $filter->name . '.' . (count($filter->allowedComparisons) > 1 ? ' Available comparisons: ' . implode(', ', $filter->allowedComparisons) : '')
            ]);
            $operation->getParameters()->add($parameter);
        }
    }

    /**
     * @param Operation $operation
     * @param Rfc14\Sort[] $sorts
     */
    private function addSortsToOperation(Operation $operation, array $sorts): void
    {
        foreach ($sorts as $sort) {
            $parameter = new Parameter([
                'name' => 'sort[' . $sort->name . ']',
                'in' => 'query',
                'type' => 'string',
                'enum' => $sort->allowedDirections,
                'required' => false,
                'description' => 'Sort the result by ' . $sort->name . '.'
            ]);
            $operation->getParameters()->add($parameter);
        }
    }

    /**
     * @param Operation $operation
     * @param Rfc14\Pagination[] $paginations
     */
    private function addPaginationsToOperation(Operation $operation, array $paginations): void
    {
        foreach ($paginations as $pagination) {
            $parameter = new Parameter([
                'name' => 'limit',
                'in' => 'query',
                'type' => 'string',
                'required' => false,
                'description' => 'Paginate the result by giving offset and limit ("limit=20,5" for offset 20 and limit 5. Offset can be emitted, so "limit=5" would give the first 5 entries.).' . ($pagination->maxEntries !== null ? ' Max allowed limit: ' . $pagination->maxEntries : '')
            ]);
            $operation->getParameters()->add($parameter);
        }
    }
}