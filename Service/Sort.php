<?php

namespace Shopping\ApiTKUrlBundle\Service;

use Doctrine\ORM\QueryBuilder;
use Shopping\ApiTKUrlBundle\Annotation as Api;
use Shopping\ApiTKUrlBundle\Input\SortField;

interface Sort
{
    /**
     * Returns all sort fields.
     *
     * @return SortField[]
     */
    public function getSortedFields(): array;

    /**
     * Returns true if this sort field was given.
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasSortedField(string $name): bool;

    /**
     * Returns the sort field for the given name.
     *
     * @param string $name
     *
     * @return SortField|null
     */
    public function getSortedField(string $name): ?SortField;

    /**
     * Checks if only allowed sort fields were given in the request;.
     *
     * @param Api\Sort[] $sorts
     */
    public function handleAllowedSorts(array $sorts): void;

    /**
     * @param QueryBuilder $queryBuilder
     */
    public function applySortedFieldsToQueryBuilder(QueryBuilder $queryBuilder): void;
}
