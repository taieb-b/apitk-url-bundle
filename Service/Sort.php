<?php

namespace Bywulf\Rfc14Bundle\Service;

use Bywulf\Rfc14Bundle\Input\SortField;
use Doctrine\ORM\QueryBuilder;
use Bywulf\Rfc14Bundle\Annotation AS Rfc14;

interface Sort {
    /**
     * Returns all sort fields.
     * 
     * @return SortField[]
     */
    public function getAll(): array;

    /**
     * Returns true if this sort field was given.
     * 
     * @param string $name
     * @return bool
     */
    public function has(string $name): bool;

    /**
     * Returns the sort field for the given name.
     * 
     * @param string $name
     * @return SortField|null
     */
    public function get(string $name): ?SortField;

    /**
     * Checks if only allowed sort fields were given in the request;
     * 
     * @param Rfc14\Sort[] $sorts
     */
    public function handleAllowed(array $sorts): void;

    /**
     * @param QueryBuilder $queryBuilder
     */
    public function applyToQueryBuilder(QueryBuilder $queryBuilder): void;
}