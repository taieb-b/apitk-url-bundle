<?php

declare(strict_types=1);

namespace Shopping\ApiTKUrlBundle\Input;

use Doctrine\ORM\QueryBuilder;
use Shopping\ApiTKUrlBundle\Annotation as ApiTK;
use Shopping\ApiTKUrlBundle\Exception\FilterException;

/**
 * Represents a given filter from the user.
 */
class FilterField implements ApplicableToQueryBuilder
{
    private string $name = '';

    private mixed $value = null;

    private string $comparison = '';

    private ?ApiTK\Filter $filter;

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): FilterField
    {
        $this->name = $name;

        return $this;
    }

    public function getValue(): mixed
    {
        if (in_array($this->getComparison(), [ApiTK\Filter::COMPARISON_IN, ApiTK\Filter::COMPARISON_NOTIN])) {
            return explode(',', $this->value);
        }

        return $this->value;
    }

    public function setValue(mixed $value): FilterField
    {
        $this->value = $value;

        return $this;
    }

    public function getComparison(): string
    {
        return $this->comparison;
    }

    public function setComparison(string $comparison): FilterField
    {
        $this->comparison = $comparison;

        return $this;
    }

    public function getFilter(): ?ApiTK\Filter
    {
        return $this->filter;
    }

    public function setFilter(?ApiTK\Filter $filter): FilterField
    {
        $this->filter = $filter;

        return $this;
    }

    private function getQueryBuilderName(QueryBuilder $queryBuilder): string
    {
        $queryBuilderName = $queryBuilder->getRootAliases()[0] . '.' . $this->getName();
        if ($this->filter !== null && $this->filter->queryBuilderName) {
            $queryBuilderName = $this->filter->queryBuilderName;
        }

        return $queryBuilderName;
    }

    private function getUniquePlaceholder(): string
    {
        return 'filter_' . $this->getName() . '_' . sha1(uniqid('', true));
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function applyToQueryBuilder(QueryBuilder $queryBuilder): void
    {
        if ($this->filter !== null && !$this->filter->autoApply) {
            return;
        }

        $parameter = $this->getUniquePlaceholder();

        switch ($this->getComparison()) {
            case ApiTK\Filter::COMPARISON_EQUALS:
                if (strtolower($this->getValue()) === '\null') {
                    $queryBuilder->andWhere($queryBuilder->expr()->isNull($this->getQueryBuilderName($queryBuilder)));
                } else {
                    $queryBuilder->andWhere($queryBuilder->expr()->eq($this->getQueryBuilderName($queryBuilder), ':' . $parameter))
                        ->setParameter($parameter, $this->getValue());
                }

                break;
            case ApiTK\Filter::COMPARISON_NOTEQUALS:
                if (strtolower($this->getValue()) === '\null') {
                    $queryBuilder->andWhere($queryBuilder->expr()->isNotNull($this->getQueryBuilderName($queryBuilder)));
                } else {
                    $queryBuilder->andWhere(
                        $queryBuilder->expr()->orX(
                            $queryBuilder->expr()->neq($this->getQueryBuilderName($queryBuilder), ':' . $parameter),
                            $queryBuilder->expr()->isNull($this->getQueryBuilderName($queryBuilder))
                        )
                    )
                    ->setParameter($parameter, $this->getValue());
                }

                break;
            case ApiTK\Filter::COMPARISON_IN:
                $queryBuilder->andWhere($queryBuilder->expr()->in($this->getQueryBuilderName($queryBuilder), ':' . $parameter))
                    ->setParameter($parameter, $this->getValue());

                break;
            case ApiTK\Filter::COMPARISON_NOTIN:
                $queryBuilder->andWhere(
                    $queryBuilder->expr()->orX(
                        $queryBuilder->expr()->notIn($this->getQueryBuilderName($queryBuilder), ':' . $parameter),
                        $queryBuilder->expr()->isNull($this->getQueryBuilderName($queryBuilder))
                    )
                )->setParameter($parameter, $this->getValue());

                break;
            case ApiTK\Filter::COMPARISON_GREATERTHAN:
                $queryBuilder->andWhere($queryBuilder->expr()->gt($this->getQueryBuilderName($queryBuilder), ':' . $parameter))
                    ->setParameter($parameter, $this->getValue());

                break;
            case ApiTK\Filter::COMPARISON_GREATERTHANEQUALS:
                $queryBuilder->andWhere($queryBuilder->expr()->gte($this->getQueryBuilderName($queryBuilder), ':' . $parameter))
                    ->setParameter($parameter, $this->getValue());

                break;
            case ApiTK\Filter::COMPARISON_LESSTHAN:
                $queryBuilder->andWhere($queryBuilder->expr()->lt($this->getQueryBuilderName($queryBuilder), ':' . $parameter))
                    ->setParameter($parameter, $this->getValue());

                break;
            case ApiTK\Filter::COMPARISON_LESSTHANEQUALS:
                $queryBuilder->andWhere($queryBuilder->expr()->lte($this->getQueryBuilderName($queryBuilder), ':' . $parameter))
                    ->setParameter($parameter, $this->getValue());

                break;
            case ApiTK\Filter::COMPARISON_LIKE:
                $queryBuilder->andWhere($queryBuilder->expr()->like($this->getQueryBuilderName($queryBuilder), ':' . $parameter))
                    ->setParameter($parameter, $this->getValue());
        }
    }

    /**
     * Returns true if the given value matches this filter.
     *
     * @throws FilterException
     */
    public function matches(mixed $value): bool
    {
        switch ($this->getComparison()) {
            case ApiTK\Filter::COMPARISON_EQUALS:
                return $value === $this->getValue();
            case ApiTK\Filter::COMPARISON_NOTEQUALS:
                return $value !== $this->getValue();
            case ApiTK\Filter::COMPARISON_IN:
                return in_array($value, $this->getValue());
            case ApiTK\Filter::COMPARISON_NOTIN:
                return !in_array($value, $this->getValue());
            case ApiTK\Filter::COMPARISON_GREATERTHAN:
                return $value > $this->getValue();
            case ApiTK\Filter::COMPARISON_GREATERTHANEQUALS:
                return $value >= $this->getValue();
            case ApiTK\Filter::COMPARISON_LESSTHAN:
                return $value < $this->getValue();
            case ApiTK\Filter::COMPARISON_LESSTHANEQUALS:
                return $value <= $this->getValue();
            default:
                throw new FilterException('Unknown comparison');
        }
    }
}
