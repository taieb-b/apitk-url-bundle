<?php

declare(strict_types=1);

namespace Shopping\ApiTKUrlBundle\Service;

use Doctrine\ORM\QueryBuilder;
use Shopping\ApiTKCommonBundle\Exception\MissingDependencyException;
use Shopping\ApiTKUrlBundle\Annotation as Api;
use Shopping\ApiTKUrlBundle\Input\FilterField;

interface Filter
{
    /**
     * Returns all filter fields.
     *
     * @return FilterField[]
     */
    public function getFilteredFields(): array;

    /**
     * Returns true if this filter field was given.
     */
    public function hasFilteredField(string $name): bool;

    /**
     * Returns the filter field for the given name.
     */
    public function getFilteredField(string $name): ?FilterField;

    /**
     * Checks if only allowed filter fields were given in the request;.
     *
     * @param Api\Filter[] $filters
     */
    public function handleAllowedFilters(array $filters): void;

    /**
     * @throws MissingDependencyException
     */
    public function applyFilteredFieldsToQueryBuilder(QueryBuilder $queryBuilder): void;
}
