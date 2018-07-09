<?php

namespace Ofeige\Rfc14Bundle\Service;

use Doctrine\ORM\QueryBuilder;
use Ofeige\ApiBundle\Service\HeaderInformation;
use Ofeige\Rfc14Bundle\Exception\PaginationException;
use Symfony\Component\HttpFoundation\RequestStack;

class Rfc14Service implements Filter, Pagination, Sort
{
    use FilterTrait;
    use SortTrait;
    use PaginationTrait;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var HeaderInformation
     */
    private $headerInformation;

    /**
     * FilterFromRequestQuery constructor.
     * @param RequestStack $requestStack
     * @param HeaderInformation $headerInformation
     */
    public function __construct(RequestStack $requestStack, HeaderInformation $headerInformation)
    {
        $this->requestStack = $requestStack;
        $this->headerInformation = $headerInformation;
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