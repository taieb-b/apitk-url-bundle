<?php

namespace Shopping\ApiFilterBundle\Service;

use Shopping\ApiFilterBundle\Input\FilterField;
use Doctrine\ORM\QueryBuilder;
use Shopping\ApiFilterBundle\Annotation AS Api;

interface Filter {
    /**
     * Returns all filter fields.
     * 
     * @return FilterField[]
     */
    public function getFilteredFields(): array;

    /**
     * Returns true if this filter field was given.
     * 
     * @param string $name
     * @return bool
     */
    public function hasFilteredField(string $name): bool;

    /**
     * Returns the filter field for the given name.
     * 
     * @param string $name
     * @return FilterField|null
     */
    public function getFilteredField(string $name): ?FilterField;

    /**
     * Checks if only allowed filter fields were given in the request;
     * 
     * @param Api\Filter[] $filters
     */
    public function handleAllowedFilters(array $filters): void;

    /**
     * @param QueryBuilder $queryBuilder
     */
    public function applyFilteredFieldsToQueryBuilder(QueryBuilder $queryBuilder): void;
}