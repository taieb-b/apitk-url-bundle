<?php

namespace Ofeige\Rfc14Bundle\Service;

use Doctrine\ORM\QueryBuilder;
use Ofeige\Rfc14Bundle\Exception\PaginationException;
use Symfony\Component\HttpFoundation\RequestStack;

class Rfc14Service implements Filter, Pagination, Sort, HeaderInformation
{
    use FilterTrait;
    use SortTrait;
    use PaginationTrait;
    use HeaderInformationTrait;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * FilterFromRequestQuery constructor.
     * @param RequestStack $requestStack
     */
    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws PaginationException
     */
    public function applyToQueryBuilder(QueryBuilder $queryBuilder): void
    {
        $this->applyFilteredFieldsToQueryBuilder($queryBuilder);
        $this->applySortedFieldsToQueryBuilder($queryBuilder);
        $this->applyPaginationToQueryBuilder($queryBuilder);
    }
}