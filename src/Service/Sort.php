<?php

declare(strict_types=1);

namespace Shopping\ApiTKUrlBundle\Service;

use Doctrine\ORM\QueryBuilder;
use Shopping\ApiTKCommonBundle\Exception\MissingDependencyException;
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
     */
    public function hasSortedField(string $name): bool;

    /**
     * Returns the sort field for the given name.
     */
    public function getSortedField(string $name): ?SortField;

    /**
     * Checks if only allowed sort fields were given in the request;.
     *
     * @param Api\Sort[] $sorts
     */
    public function handleAllowedSorts(array $sorts): void;

    /**
     * @throws MissingDependencyException
     */
    public function applySortedFieldsToQueryBuilder(QueryBuilder $queryBuilder): void;
}
