<?php
declare(strict_types=1);

namespace Ofeige\Rfc14Bundle\Service;

use Doctrine\ORM\QueryBuilder;
use Ofeige\Rfc14Bundle\Exception\FilterException;
use Ofeige\Rfc14Bundle\Input\FilterField;
use Ofeige\Rfc14Bundle\Annotation as Rfc14;
use Symfony\Component\HttpFoundation\RequestStack;

trait FilterTrait
{

    /**
     * @var FilterField[]
     */
    private $filterFields;

    /**
     * @var Rfc14\Filter[]
     */
    private $filters = [];

    /**
     * @var RequestStack
     */
    private $requestStack;

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
                        'Filter "%s" with comparison "%s" and value "%s" is not allowed in this request. Available filters: %s',
                        $filterField->getName(),
                        $filterField->getComparison(),
                        $filterField->getValue(),
                        implode(', ', array_map(function(Rfc14\Filter $filter) {
                            $hints = [];
                            $hints[] = 'comparisons: ' . implode(', ', $filter->allowedComparisons);
                            if (count($filter->enum) > 0) {
                                $hints[] = 'values: ' . implode(', ', $filter->enum);
                            }

                            return $filter->name . ' (' . implode(' // ', $hints) . ')';
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

            if (
                in_array($filterField->getComparison(), $filter->allowedComparisons) &&
                (count($filter->enum) === 0 || in_array($filterField->getValue(), $filter->enum))
            ) {
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
        $masterRequest = $this->requestStack->getMasterRequest();
        $requestFilters = $masterRequest->query->get('filter');
        if (is_array($requestFilters)) {
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
    }

    private function loadFiltersFromAttributes(): void
    {
        $masterRequest = $this->requestStack->getMasterRequest();
        foreach ($masterRequest->attributes->getIterator() as $key => $value) {
            if ($this->getFilterByName($key) === null) {
                continue;
            }

            $filterField = new FilterField();
            $filterField->setName($key)
                ->setValue($value)
                ->setComparison(Rfc14\Filter::COMPARISON_EQUALS)
                ->setFilter($this->getFilterByName($key));

            $this->filterFields[] = $filterField;
        }
    }

    /**
     * @return FilterField[]
     */
    public function getFilteredFields(): array
    {
        if ($this->filterFields === null) {
            $this->filterFields = [];
            $this->loadFiltersFromQuery();
            $this->loadFiltersFromAttributes();
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
     */
    public function applyFilteredFieldsToQueryBuilder(QueryBuilder $queryBuilder): void
    {
        foreach ($this->getFilteredFields() as $filterField) {
            $filterField->applyToQueryBuilder($queryBuilder);
        }
    }
}