<?php

namespace Ofeige\Rfc14Bundle\Service;


use Doctrine\ORM\QueryBuilder;
use Ofeige\Rfc14Bundle\Annotation as Rfc14;
use Ofeige\Rfc14Bundle\Exception\FilterException;
use Ofeige\Rfc14Bundle\Exception\PaginationException;
use Ofeige\Rfc14Bundle\Exception\SortException;
use Ofeige\Rfc14Bundle\Input\FilterField;
use Ofeige\Rfc14Bundle\Input\SortField;
use Symfony\Component\HttpFoundation\RequestStack;

class Rfc14Service implements Filter, Pagination, Sort
{
    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var FilterField[]
     */
    private $filterFields;

    /**
     * @var Rfc14\Filter[]
     */
    private $filters = [];

    /**
     * @var SortField[]
     */
    private $sortFields;

    /**
     * @var Rfc14\Sort[]
     */
    private $sorts = [];

    /**
     * @var Rfc14\Pagination
     */
    private $pagination;

    /**
     * @var int|null
     */
    private $paginationOffset;

    /**
     * @var int|null
     */
    private $paginationLimit;

    /**
     * @var int
     */
    private $paginationTotal;

    /**
     * FilterFromRequestQuery constructor.
     * @param RequestStack $requestStack
     */
    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    /**
     * Checks if only allowed filter fields were given in the request;
     *
     * @param Rfc14\Filter[] $filters
     * @throws FilterException
     */
    public function handleAllowedFilters(array $filters): void
    {
        $this->filters = $filters;

        foreach ($this->getFilteredFields() as $filterField) {
            if (!$this->isAllowedFilterField($filterField)) {
                throw new FilterException(
                    sprintf(
                        'Filter "%s" with comparison "%s" is not allowed in this request. Available filters: %s',
                        $filterField->getName(),
                        $filterField->getComparison(),
                        implode(', ', array_map(function(Rfc14\Filter $filter) {
                            return $filter->name . ' (' . implode(', ', $filter->allowedComparisons) . ')';
                        }, $filters))
                    )
                );
            }
        }
    }

    /**
     * @param FilterField $filterField
     * @return bool
     */
    private function isAllowedFilterField(FilterField $filterField): bool
    {
        foreach ($this->filters as $filter) {
            if ($filter->name !== $filterField->getName()) {
                continue;
            }

            if (in_array($filterField->getComparison(), $filter->allowedComparisons)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks if only allowed sort fields were given in the request;
     *
     * @param Rfc14\Sort[] $sorts
     * @throws SortException
     */
    public function handleAllowedSorts(array $sorts): void
    {
        $this->sorts = $sorts;

        foreach ($this->getSortedFields() as $sortField) {
            if (!$this->isAllowedSortField($sortField)) {
                throw new SortException(
                    sprintf(
                        'Sort "%s" with direction "%s" is not allowed in this request. Available sorts: %s',
                        $sortField->getName(),
                        $sortField->getDirection(),
                        implode(', ', array_map(function(Rfc14\Sort $sort) {
                            return $sort->name . ' (' . implode(', ', $sort->allowedDirections) . ')';
                        }, $sorts))
                    )
                );
            }
        }
    }

    /**
     * @param SortField $sortField
     * @return bool
     */
    private function isAllowedSortField(SortField $sortField): bool
    {
        foreach ($this->sorts as $sort) {
            if ($sort->name !== $sortField->getName()) {
                continue;
            }

            if (in_array($sortField->getDirection(), $sort->allowedDirections)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks if only allowed sort fields were given in the request;
     *
     * @param Rfc14\Pagination $pagination
     */
    public function handleIsPaginatable(Rfc14\Pagination $pagination): void
    {
        $this->pagination = $pagination;
    }

    /**
     * @param string $name
     * @return Rfc14\Filter|null
     */
    private function getFilterByName(string $name): ?Rfc14\Filter
    {
        foreach ($this->filters as $filter) {
            if ($filter->name === $name) {
                return $filter;
            }
        }

        return null;
    }

    private function loadFiltersFromQuery(): void
    {
        $this->filterFields = [];

        $requestFilters = $this->requestStack->getMasterRequest()->query->get('filter');
        if (!is_array($requestFilters)) {
            return;
        }

        foreach ($requestFilters as $name => $limitations) {
            foreach ($limitations as $comparison => $value) {
                $filterField = new FilterField();
                $filterField->setName($name)
                    ->setValue($value)
                    ->setComparison($comparison)
                    ->setFilter($this->getFilterByName($name));

                $this->filterFields[] = $filterField;
            }
        }
    }

    /**
     * @return FilterField[]
     */
    public function getFilteredFields(): array
    {
        if ($this->filterFields === null) {
            $this->loadFiltersFromQuery();
        }

        return $this->filterFields;
    }

    /**
     * Returns true if this filter field was given.
     *
     * @param string $name
     * @return bool
     */
    public function hasFilteredField(string $name): bool
    {
        return $this->getFilteredField($name) !== null;
    }

    /**
     * Returns the filter field for the given name.
     *
     * @param string $name
     * @return FilterField|null
     */
    public function getFilteredField(string $name): ?FilterField
    {
        foreach ($this->getFilteredFields() as $filterField) {
            if ($filterField->getName() === $name) {
                return $filterField;
            }
        }

        return null;
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws PaginationException
     */
    public function applyToQueryBuilder(QueryBuilder $queryBuilder): void
    {
        //Filter
        foreach ($this->getFilteredFields() as $filterField) {
            $filterField->applyToQueryBuilder($queryBuilder);
        }

        //Sort
        foreach ($this->getSortedFields() as $sortField) {
            $sortField->applyToQueryBuilder($queryBuilder);
        }

        //Pagination
        if ($this->pagination !== null) {
            $totalQueryBuilder = clone $queryBuilder;
            $totalQueryBuilder->select('COUNT(DISTINCT ' . $totalQueryBuilder->getRootAliases()[0] . ')');
            $this->paginationTotal = (int)$totalQueryBuilder->getQuery()->getSingleScalarResult();

            $queryBuilder->setMaxResults($this->getPaginationLimit());
            $queryBuilder->setFirstResult($this->getPaginationOffset());
        }
    }


    /**
     * @throws PaginationException
     */
    private function parsePagination(): void
    {
        $parameter = $this->requestStack->getMasterRequest()->query->get('limit');

        if ($parameter !== null) {
            if ($this->pagination === null) {
                throw new PaginationException('Limit parameter not available in current request.');
            }

            $parts = explode(',', $parameter);
            if (count($parts) === 1) {
                $this->paginationLimit = (int) $parts[0];
            } elseif (count($parts) === 2) {
                $this->paginationOffset = (int) $parts[0];
                $this->paginationLimit = (int) $parts[1];
            } else {
                throw new PaginationException('Invalid limit parameter. Allowed formats: limit=[limit], limit=[offset],[limit]');
            }
        }
    }

    /**
     * @return int
     * @throws PaginationException
     */
    public function getPaginationOffset(): int
    {
        $this->parsePagination();

        return $this->paginationOffset ?? 0;
    }

    /**
     * @return int|null
     * @throws PaginationException
     */
    public function getPaginationLimit(): ?int
    {
        $this->parsePagination();

        return $this->paginationLimit;
    }

    /**
     * Only has to be set if applyToQueryBuilder() is not used.
     *
     * @param int $paginationTotal
     */
    public function setPaginationTotal(int $paginationTotal): void
    {
        $this->paginationTotal = $paginationTotal;
    }

    /**
     * @return int|null
     */
    public function getPaginationTotal(): ?int
    {
        return $this->paginationTotal;
    }

    /**
     * @param string $name
     * @return Rfc14\Sort|null
     */
    private function getSortByName(string $name): ?Rfc14\Sort
    {
        foreach ($this->sorts as $sort) {
            if ($sort->name === $name) {
                return $sort;
            }
        }

        return null;
    }

    private function loadSortsFromQuery(): void
    {
        $this->sortFields = [];

        $requestSorts = $this->requestStack->getMasterRequest()->query->get('sort');
        if (!is_array($requestSorts)) {
            return;
        }

        foreach ($requestSorts as $name => $direction) {
            $sortField = new SortField();
            $sortField->setName($name)
                ->setDirection($direction)
                ->setSort($this->getSortByName($name));

            $this->sortFields[] = $sortField;
        }
    }

    /**
     * @return SortField[]
     */
    public function getSortedFields(): array
    {
        if ($this->sortFields === null) {
            $this->loadSortsFromQuery();
        }

        return $this->sortFields;
    }

    /**
     * Returns true if this sort field was given.
     *
     * @param string $name
     * @return bool
     */
    public function hasSortedField(string $name): bool
    {
        return $this->getSortedField($name) !== null;
    }

    /**
     * Returns the sort field for the given name.
     *
     * @param string $name
     * @return SortField|null
     */
    public function getSortedField(string $name): ?SortField
    {
        foreach ($this->getSortedFields() as $sortField) {
            if ($sortField->getName() === $name) {
                return $sortField;
            }
        }

        return null;
    }
}