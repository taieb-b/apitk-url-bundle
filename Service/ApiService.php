<?php

namespace Shopping\ApiTKUrlBundle\Service;

use Doctrine\ORM\QueryBuilder;
use Shopping\ApiHelperBundle\Service\HeaderInformation;
use Shopping\ApiTKUrlBundle\Exception\PaginationException;
use Symfony\Component\HttpFoundation\RequestStack;

class ApiService implements Filter, Pagination, Sort
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