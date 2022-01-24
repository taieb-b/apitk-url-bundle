<?php

declare(strict_types=1);

namespace Shopping\ApiTKUrlBundle\Describer;

use Doctrine\Common\Annotations\AnnotationReader;
use Nelmio\ApiDocBundle\OpenApiPhp\Util;
use Nelmio\ApiDocBundle\RouteDescriber\RouteDescriberInterface;
use OpenApi\Annotations as OA;
use ReflectionAttribute;
use ReflectionMethod;
use Shopping\ApiTKCommonBundle\Describer\RouteDescriberTrait;
use Shopping\ApiTKUrlBundle\Annotation as Api;
use Symfony\Component\Routing\Route;

class AnnotationDescriber implements RouteDescriberInterface
{
    use RouteDescriberTrait;

    public function __construct(
        private AnnotationReader $annotationReader
    ) {
    }

    public function describe(OA\OpenApi $api, Route $route, ReflectionMethod $reflectionMethod): void
    {
        /** @var Api\Filter[] $filters */
        $filters = $this->getAnnotations($reflectionMethod, Api\Filter::class);
        /** @var Api\Sort[] $sorts */
        $sorts = $this->getAnnotations($reflectionMethod, Api\Sort::class);
        /** @var Api\Pagination[] $paginations */
        $paginations = $this->getAnnotations($reflectionMethod, Api\Pagination::class);

        foreach ($this->getOperations($api, $route) as $operation) {
            if (!empty($filters)) {
                $this->addFiltersToOperation($operation, $filters, $route);
            }

            if (!empty($sorts)) {
                $this->addSortsToOperation($operation, $sorts);
            }

            if (!empty($paginations)) {
                $this->addPaginationsToOperation($operation, $paginations);
            }
        }
    }

    /**
     * @param Api\Sort[] $sorts
     */
    private function addSortsToOperation(OA\Operation $operation, array $sorts): void
    {
        foreach ($sorts as $sort) {
            $parameter = Util::getOperationParameter($operation, 'sort[' . $sort->name . ']', 'query');

            $parameter->required = false;
            $parameter->description = 'Sort the result by ' . $sort->name . '.';

            /** @var OA\Schema $schema */
            $schema = Util::getChild($parameter, OA\Schema::class);
            $schema->type = 'string';
            $schema->enum = $sort->allowedDirections;
        }
    }

    /**
     * @param Api\Pagination[] $paginations
     */
    private function addPaginationsToOperation(OA\Operation $operation, array $paginations): void
    {
        foreach ($paginations as $pagination) {
            $parameter = Util::getOperationParameter($operation, 'limit', 'query');

            $parameter->description = 'Paginate the result by giving offset and limit '
                . '("limit=20,5" for offset 20 and limit 5. Offset can be emitted, so "limit=5" would give the first 5 entries.).'
                . ($pagination->maxEntries !== null ? ' Max allowed limit: ' . $pagination->maxEntries : '');
            $parameter->required = false;

            /** @var OA\Schema $schema */
            $schema = Util::getChild($parameter, OA\Schema::class);
            $schema->type = 'string';

            /** @var OA\Response $response */
            $response = Util::getCollectionItem($operation, OA\Response::class, ['response' => 200]);

            /** @var OA\Header $header */
            $header = Util::getCollectionItem($response, OA\Header::class, ['header' => 'x-apitk-pagination-total']);
            $header->description = 'Total count of entries';

            /** @var OA\Schema $schema */
            $schema = Util::getChild($header, OA\Schema::class, ['type' => 'integer']);
            $header->schema = $schema;
        }
    }

    /**
     * @param Api\Filter[] $filters
     */
    private function addFiltersToOperation(OA\Operation $operation, array $filters, Route $route): void
    {
        $routePlaceholders = [];
        preg_match_all('/{([^}]+)}/', $route->getPath(), $routePlaceholders);
        $routePlaceholders = $routePlaceholders[1];

        foreach ($filters as $filter) {
            if (in_array($filter->name, $routePlaceholders)) {
                $parameter = Util::getOperationParameter($operation, $filter->name, 'path');
                $parameter->description = 'Only show entries, which match this ' . $filter->name . '.';
                $parameter->required = true;
            } else {
                $parameter = Util::getOperationParameter($operation, 'filter[' . $filter->name . '][' . $filter->allowedComparisons[0] . ']', 'query');

                $description = 'Only show entries, which match this ' . $filter->name . '.';
                if (count($filter->allowedComparisons) > 1) {
                    $description .= ' Available comparisons: ' . implode(', ', $filter->allowedComparisons);
                }

                $parameter->description = $description;
                $parameter->required = false;
            }

            /** @var OA\Schema $schema */
            $schema = Util::getChild($parameter, OA\Schema::class);
            if (count($filter->enum) > 0) {
                $schema->type = 'enum';
                $schema->enum = $filter->enum;
            } else {
                $schema->type = 'string';
            }
        }
    }

    /**
     * @param class-string $annotationClass
     *
     * @return mixed[]
     */
    private function getAnnotations(ReflectionMethod $method, string $annotationClass): array
    {
        $annotations = $this->annotationReader->getMethodAnnotations($method);
        $annotations = array_filter($annotations, static function ($value) use ($annotationClass) {
            return $value instanceof $annotationClass;
        });

        $attributes = array_map(
            static function (ReflectionAttribute $attribute): object { return $attribute->newInstance(); },
            $method->getAttributes($annotationClass, ReflectionAttribute::IS_INSTANCEOF),
        );

        return array_merge($attributes, $annotations);
    }
}
