<?php

namespace Shopping\ApiFilterBundle\Describer;

use Doctrine\Common\Annotations\Reader;
use EXSyst\Component\Swagger\Operation;
use EXSyst\Component\Swagger\Parameter;
use EXSyst\Component\Swagger\Swagger;
use Nelmio\ApiDocBundle\Describer\DescriberInterface;
use Nelmio\ApiDocBundle\Util\ControllerReflector;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouteCollection;
use Shopping\ApiFilterBundle\Annotation AS Rfc14;

/**
 * Class AnnotationDescriber
 *
 * Will auto generate documentation for the filters, sorts and pagination annotated in the called controller action.
 *
 * @package Shopping\ApiFilterBundle\Describer
 */
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

    /**
     * @param RouteCollection $routeCollection
     * @param ControllerReflector $controllerReflector
     * @param Reader $reader
     */
    public function __construct(RouteCollection $routeCollection, ControllerReflector $controllerReflector, Reader $reader)
    {
        $this->routeCollection = $routeCollection;
        $this->controllerReflector = $controllerReflector;
        $this->reader = $reader;
    }

    /**
     * @param Swagger $api
     */
    public function describe(Swagger $api)
    {
        $paths = $api->getPaths();
        foreach ($paths as $uri => $path) {
            foreach ($path->getMethods() as $method) {
                /** @var Operation $operation */
                $operation = $path->getOperation($method);

                foreach ($this->getMethodsToParse() as $classMethod => list($methodPath, $httpMethods)) {
                    if ($methodPath === $uri && in_array($method, $httpMethods)) {
                        $methodAnnotations = $this->reader->getMethodAnnotations($classMethod);

                        /** @var Rfc14\Filter[] $filters */
                        $filters = array_filter($methodAnnotations, function($annotation) { return $annotation instanceof Rfc14\Filter; });
                        /** @var Route[] $routes */
                        $routes = array_filter($methodAnnotations, function($annotation) { return $annotation instanceof Route; });
                        $this->addFiltersToOperation($operation, $filters, $routes);

                        /** @var Rfc14\Sort[] $sorts */
                        $sorts = array_filter($methodAnnotations, function($annotation) { return $annotation instanceof Rfc14\Sort; });
                        $this->addSortsToOperation($operation, $sorts);

                        //Pagination
                        /** @var Rfc14\Pagination[] $paginations */
                        $paginations = array_filter($methodAnnotations, function($annotation) { return $annotation instanceof Rfc14\Pagination; });
                        $this->addPaginationsToOperation($operation, $paginations);
                    }
                }
            }
        }
    }

    /**
     * @return \Generator
     */
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

    /**
     * @param string $path
     * @return string
     */
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
     * @param Route[] $routes
     */
    private function addFiltersToOperation(Operation $operation, array $filters, array $routes): void
    {
        $routePlaceholders = [];
        foreach ($routes as $route) {
            $matches = [];
            preg_match_all('/{([^}]+)}/', $route->getPath(), $matches);
            $routePlaceholders = array_merge($routePlaceholders, $matches[1]);
        }

        foreach ($filters as $filter) {
            if (in_array($filter->name, $routePlaceholders)) {
                $parameter = new Parameter([
                    'name' => $filter->name,
                    'in' => 'path',
                    'type' => 'string',
                    'required' => true,
                    'description' => 'Only show entries, which match this ' . $filter->name . '.'
                ]);
            } else {
                $parameter = new Parameter([
                    'name' => 'filter[' . $filter->name . '][' . $filter->allowedComparisons[0] . ']',
                    'in' => 'query',
                    'type' => 'string',
                    'required' => false,
                    'description' => 'Only show entries, which match this ' . $filter->name . '.' . (count($filter->allowedComparisons) > 1 ? ' Available comparisons: ' . implode(', ', $filter->allowedComparisons) : '')
                ]);
            }
            if (count($filter->enum) > 0) {
                $parameter->setEnum($filter->enum);
            }

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