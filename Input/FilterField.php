<?php
namespace Ofeige\Rfc14Bundle\Input;

use Ofeige\Rfc14Bundle\Annotation as Rfc14;
use Doctrine\ORM\QueryBuilder;

class FilterField implements ApplyableToQueryBuilder
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var mixed
     */
    private $value;

    /**
     * @var string
     */
    private $comparison;

    /**
     * @var Rfc14\Filter|null
     */
    private $filter;

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return FilterField
     */
    public function setName(string $name): FilterField
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        if (in_array($this->getComparison(), [Rfc14\Filter::COMPARISON_IN, Rfc14\Filter::COMPARISON_NOTIN])) {
            return explode(',', $this->getValue());
        }

        return $this->value;
    }

    /**
     * @param mixed $value
     * @return FilterField
     */
    public function setValue($value): FilterField
    {
        $this->value = $value;
        return $this;
    }

    /**
     * @return string
     */
    public function getComparison(): string
    {
        return $this->comparison;
    }

    /**
     * @param string $comparison
     * @return FilterField
     */
    public function setComparison(string $comparison): FilterField
    {
        $this->comparison = $comparison;
        return $this;
    }

    /**
     * @return Rfc14\Filter|null
     */
    public function getFilter(): ?Rfc14\Filter
    {
        return $this->filter;
    }

    /**
     * @param Rfc14\Filter|null $filter
     * @return FilterField
     */
    public function setFilter(?Rfc14\Filter $filter): FilterField
    {
        $this->filter = $filter;
        return $this;
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @return string
     */
    private function getQueryBuilderName(QueryBuilder $queryBuilder) {
        $queryBuilderName = $queryBuilder->getRootAliases()[0] . '.' . $this->getName();
        if ($this->filter->queryBuilderName) {
            $queryBuilderName = $this->filter->queryBuilderName;
        }

        return $queryBuilderName;
    }

    private function getUniquePlaceholder(): string
    {
        return 'filter_' . $this->getName() . '_' . uniqid();
    }

    /**
     * @param QueryBuilder $queryBuilder
     */
    public function applyToQueryBuilder(QueryBuilder $queryBuilder): void
    {
        $parameter = $this->getUniquePlaceholder();

        switch ($this->getComparison()) {
            case Rfc14\Filter::COMPARISON_EQUALS:
                $queryBuilder->andWhere($queryBuilder->expr()->eq($this->getQueryBuilderName($queryBuilder), ':' . $parameter));
                break;

            case Rfc14\Filter::COMPARISON_NOTEQUALS:
                $queryBuilder->andWhere($queryBuilder->expr()->neq($this->getQueryBuilderName($queryBuilder), ':' . $parameter));
                break;

            case Rfc14\Filter::COMPARISON_IN:
                $queryBuilder->andWhere($queryBuilder->expr()->in($this->getQueryBuilderName($queryBuilder), ':' . $parameter));
                break;

            case Rfc14\Filter::COMPARISON_NOTIN:
                $queryBuilder->andWhere($queryBuilder->expr()->notIn($this->getQueryBuilderName($queryBuilder), ':' . $parameter));
                break;

            case Rfc14\Filter::COMPARISON_GREATERTHAN:
                $queryBuilder->andWhere($queryBuilder->expr()->gt($this->getQueryBuilderName($queryBuilder), ':' . $parameter));
                break;

            case Rfc14\Filter::COMPARISON_GREATERTHANEQUALS:
                $queryBuilder->andWhere($queryBuilder->expr()->gte($this->getQueryBuilderName($queryBuilder), ':' . $parameter));
                break;

            case Rfc14\Filter::COMPARISON_LESSTHAN:
                $queryBuilder->andWhere($queryBuilder->expr()->lt($this->getQueryBuilderName($queryBuilder), ':' . $parameter));
                break;

            case Rfc14\Filter::COMPARISON_LESSTHANEQUALS:
                $queryBuilder->andWhere($queryBuilder->expr()->lte($this->getQueryBuilderName($queryBuilder), ':' . $parameter));
                break;

        }
        $queryBuilder->setParameter($parameter, $this->getValue());
    }
}