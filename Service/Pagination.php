<?php
/**
 * Created by PhpStorm.
 * User: Wulf
 * Date: 24.06.2018
 * Time: 02:17
 */

namespace Shopping\ApiFilterBundle\Service;


use Doctrine\ORM\QueryBuilder;
use Shopping\ApiFilterBundle\Annotation AS Api;

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
     */
    public function applyPaginationToQueryBuilder(QueryBuilder $queryBuilder): void;

    /**
     * Checks if only allowed sort fields were given in the request;
     *
     * @param Api\Pagination $pagination
     */
    public function handleIsPaginatable(Api\Pagination $pagination): void;
}