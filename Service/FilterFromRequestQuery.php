<?php

namespace Ofeige\Rfc14Bundle\Service;

use Ofeige\Rfc14Bundle\Annotation AS Rfc14;
use Ofeige\Rfc14Bundle\Input\FilterField;
use Ofeige\Rfc14Bundle\Exception\FilterException;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\RequestStack;

class FilterFromRequestQuery implements Filter {

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
    private $filters;

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
     * @throws \Exception
     */
    public function handleAllowed(array $filters): void
    {
        $this->filters = $filters;

        foreach ($this->getAll() as $filterField) {
            if (!$this->isAllowedField($filterField)) {
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
    private function isAllowedField(FilterField $filterField): bool
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
        foreach ($this->requestStack->getMasterRequest()->query->get('filter') as $name => $limitations) {
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
    public function getAll(): array
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
    public function has(string $name): bool
    {
        return $this->get($name) !== null;
    }

    /**
     * Returns the filter field for the given name.
     *
     * @param string $name
     * @return FilterField|null
     */
    public function get(string $name): ?FilterField
    {
        foreach ($this->getAll() as $filterField) {
            if ($filterField->getName() === $name) {
                return $filterField;
            }
        }

        return null;
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @throws \Exception
     */
    public function applyToQueryBuilder(QueryBuilder $queryBuilder): void
    {
        foreach ($this->getAll() as $filterField) {
            $filterField->applyToQueryBuilder($queryBuilder);
        }
    }
}