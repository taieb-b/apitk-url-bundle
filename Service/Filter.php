<?php

namespace Ofeige\Rfc14Bundle\Service;

use Ofeige\Rfc14Bundle\Input\FilterField;
use Doctrine\ORM\QueryBuilder;
use Ofeige\Rfc14Bundle\Annotation AS Rfc14;

interface Filter {
    /**
     * Returns all filter fields.
     * 
     * @return FilterField[]
     */
    public function getAll(): array;

    /**
     * Returns true if this filter field was given.
     * 
     * @param string $name
     * @return bool
     */
    public function has(string $name): bool;

    /**
     * Returns the filter field for the given name.
     * 
     * @param string $name
     * @return FilterField|null
     */
    public function get(string $name): ?FilterField;

    /**
     * Checks if only allowed filter fields were given in the request;
     * 
     * @param Rfc14\Filter[] $filters
     */
    public function handleAllowed(array $filters): void;

    /**
     * @param QueryBuilder $queryBuilder
     */
    public function applyToQueryBuilder(QueryBuilder $queryBuilder): void;
}