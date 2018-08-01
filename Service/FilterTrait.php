<?php
declare(strict_types=1);

namespace Shopping\ApiFilterBundle\Service;

use Doctrine\ORM\QueryBuilder;
use Shopping\ApiFilterBundle\Exception\FilterException;
use Shopping\ApiFilterBundle\Input\FilterField;
use Shopping\ApiFilterBundle\Annotation as Api;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Trait FilterTrait
 *
 * Filter specific methods for the ApiService.
 *
 * @package Shopping\ApiFilterBundle\Service
 */
trait FilterTrait
{
    /**
     * @var FilterField[]
     */
    private $filterFields;

    /**
     * @var Api\Filter[]
     */
    private $filters = [];

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * Checks if only allowed filter fields were given in the request. Will be called by the event listener.
     *
     * @param Api\Filter[] $filters
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
                        implode(', ', array_map(function(Api\Filter $filter) {
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
     * Validates a requested filter field against the annotated allowed filters.
     *
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
     * Returns the annotated filter by filter name.
     *
     * @param string $name
     * @return Api\Filter|null
     */
    private function getFilterByName(string $name): ?Api\Filter
    {
        foreach ($this->filters as $filter) {
            if ($filter->name === $name) {
                return $filter;
            }
        }

        return null;
    }

    /**
     * Reads the requested filter fields by the query party of the url.
     */
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

    /**
     * Reads the requested filter fields by the placeholder parts of the route.
     */
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
                ->setComparison(Api\Filter::COMPARISON_EQUALS)
                ->setFilter($this->getFilterByName($key));

            $this->filterFields[] = $filterField;
        }
    }

    /**
     * Returns all requested filter fields from the client.
     *
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
     * Returns true if this filtering for this filter was requested by the user.
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
     * Applies all requested filter fields to the query builder.
     *
     * @param QueryBuilder $queryBuilder
     */
    public function applyFilteredFieldsToQueryBuilder(QueryBuilder $queryBuilder): void
    {
        foreach ($this->getFilteredFields() as $filterField) {
            $filterField->applyToQueryBuilder($queryBuilder);
        }
    }
}