<?php

declare(strict_types=1);

namespace Shopping\ApiTKUrlBundle\Service;

use Doctrine\ORM\QueryBuilder;
use Shopping\ApiTKCommonBundle\Exception\MissingDependencyException;
use Shopping\ApiTKUrlBundle\Annotation as Api;

interface Pagination
{
    public function getPaginationOffset(): int;

    public function getPaginationLimit(): ?int;

    public function getPaginationTotal(): ?int;

    /**
     * @throws MissingDependencyException
     */
    public function applyPaginationToQueryBuilder(QueryBuilder $queryBuilder): void;

    /**
     * Checks if only allowed sort fields were given in the request;.
     */
    public function handleIsPaginatable(Api\Pagination $pagination): void;
}
