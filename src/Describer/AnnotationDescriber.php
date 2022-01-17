<?php

declare(strict_types=1);

namespace Shopping\ApiTKUrlBundle\Describer;

use EXSyst\Component\Swagger\Operation;
use EXSyst\Component\Swagger\Parameter;
use EXSyst\Component\Swagger\Path;
use Shopping\ApiTKCommonBundle\Describer\AbstractDescriber;
use Shopping\ApiTKUrlBundle\Annotation as Api;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class AnnotationDescriber.
 *
 * Will auto generate documentation for the filters, sorts and pagination annotated in the called controller action.
 */
class AnnotationDescriber extends AbstractDescriber
{
    protected function handleOperation(
        Operation $operation,
        \ReflectionMethod $classMethod,
        Path $path,
        string $method
    ): void {
        $methodAnnotations = $this->reader->getMethodAnnotations($classMethod);

        /** @var Api\Filter[] $filters */
        $filters = array_filter($methodAnnotations, function ($annotation) { return $annotation instanceof Api\Filter; });
        /** @var Route[] $routes */
        $routes = array_filter($methodAnnotations, function ($annotation) { return $annotation instanceof Route; });
        $this->addFiltersToOperation($operation, $filters, $routes);

        /** @var Api\Sort[] $sorts */
        $sorts = array_filter($methodAnnotations, function ($annotation) { return $annotation instanceof Api\Sort; });
        $this->addSortsToOperation($operation, $sorts);

        //Pagination
        /** @var Api\Pagination[] $paginations */
        $paginations = array_filter($methodAnnotations, function ($annotation) { return $annotation instanceof Api\Pagination; });
        $this->addPaginationsToOperation($operation, $paginations);
    }

    /**
     * @param Api\Filter[] $filters
     * @param Route[]      $routes
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
                    'description' => 'Only show entries, which match this ' . $filter->name . '.',
                ]);
            } else {
                $parameter = new Parameter([
                    'name' => 'filter[' . $filter->name . '][' . $filter->allowedComparisons[0] . ']',
                    'in' => 'query',
                    'type' => 'string',
                    'required' => false,
                    'description' => 'Only show entries, which match this ' . $filter->name . '.' . (count($filter->allowedComparisons) > 1 ? ' Available comparisons: ' . implode(', ', $filter->allowedComparisons) : ''),
                ]);
            }
            if (count($filter->enum) > 0) {
                $parameter->setEnum($filter->enum);
            }

            $operation->getParameters()->add($parameter);
        }
    }

    /**
     * @param Api\Sort[] $sorts
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
                'description' => 'Sort the result by ' . $sort->name . '.',
            ]);
            $operation->getParameters()->add($parameter);
        }
    }

    /**
     * @param Api\Pagination[] $paginations
     */
    private function addPaginationsToOperation(Operation $operation, array $paginations): void
    {
        foreach ($paginations as $pagination) {
            $parameter = new Parameter([
                'name' => 'limit',
                'in' => 'query',
                'type' => 'string',
                'required' => false,
                'description' => 'Paginate the result by giving offset and limit ("limit=20,5" for offset 20 and limit 5. Offset can be emitted, so "limit=5" would give the first 5 entries.).' . ($pagination->maxEntries !== null ? ' Max allowed limit: ' . $pagination->maxEntries : ''),
            ]);
            $operation->getParameters()->add($parameter);

            $headerInformation = [
                'x-apitk-pagination-total' => [
                    'description' => 'Total count of entries',
                    'type' => 'integer',
                ],
            ];

            $response = $operation->getResponses()->get(200);
            $response->merge(['headers' => $headerInformation]);

            $operation->getResponses()->set(200, $response);
        }
    }
}
