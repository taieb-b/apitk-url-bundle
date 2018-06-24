<?php
/**
 * Created by PhpStorm.
 * User: Wulf
 * Date: 24.06.2018
 * Time: 02:17
 */

namespace Bywulf\Rfc14Bundle\Service;


use Doctrine\ORM\QueryBuilder;
use Bywulf\Rfc14Bundle\Annotation AS Rfc14;

interface Pagination
{
    /**
     * @return int
     */
    public function getOffset(): int;

    /**
     * @return int|null
     */
    public function getLimit(): ?int;

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