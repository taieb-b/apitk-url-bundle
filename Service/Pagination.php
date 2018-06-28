<?php
/**
 * Created by PhpStorm.
 * User: Wulf
 * Date: 24.06.2018
 * Time: 02:17
 */

namespace Ofeige\Rfc14Bundle\Service;


use Doctrine\ORM\QueryBuilder;
use Ofeige\Rfc14Bundle\Annotation AS Rfc14;

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
    public function applyToQueryBuilder(QueryBuilder $queryBuilder): void;

    /**
     * Checks if only allowed sort fields were given in the request;
     *
     * @param Rfc14\Pagination $pagination
     */
    public function handleIsPaginatable(Rfc14\Pagination $pagination): void;
}