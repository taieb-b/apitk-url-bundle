<?php

namespace Shopping\ApiTKUrlBundle\Service;

use Doctrine\ORM\QueryBuilder;
use Shopping\ApiTKCommonBundle\Exception\MissingDependencyException;
use Shopping\ApiTKUrlBundle\Annotation as Api;

interface Pagination
{
    /**
     * @return int
     */
    public function getPaginationOffset(): int;

    /**
     * @return int|null
     */
    public function getPaginationLimit(): ?int;

    /**
     * @return int|null
     */
    public function getPaginationTotal(): ?int;

    /**
     * @param QueryBuilder $queryBuilder
     *
     * @throws MissingDependencyException
     */
    public function applyPaginationToQueryBuilder(QueryBuilder $queryBuilder): void;

    /**
     * Checks if only allowed sort fields were given in the request;.
     *
     * @param Api\Pagination $pagination
     */
    public function handleIsPaginatable(Api\Pagination $pagination): void;
}
