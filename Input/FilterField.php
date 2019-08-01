<?php
namespace Shopping\ApiTKUrlBundle\Input;

use Shopping\ApiTKUrlBundle\Annotation as ApiTK;
use Doctrine\ORM\QueryBuilder;
use Shopping\ApiTKUrlBundle\Exception\FilterException;

/**
 * Class FilterField
 *
 * Represents a given filter from the user.
 *
 * @package Shopping\ApiTKUrlBundle\Input
 */
class FilterField implements ApplicableToQueryBuilder
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
     * @var ApiTK\Filter|null
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
        if (in_array($this->getComparison(), [ApiTK\Filter::COMPARISON_IN, ApiTK\Filter::COMPARISON_NOTIN])) {
            return explode(',', $this->value);
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
     * @return ApiTK\Filter|null
     */
    public function getFilter(): ?ApiTK\Filter
    {
        return $this->filter;
    }

    /**
     * @param ApiTK\Filter|null $filter
     *
     * @return FilterField
     */
    public function setFilter(?ApiTK\Filter $filter): FilterField
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

    /**
     * @return string
     */
    private function getUniquePlaceholder(): string
    {
        return 'filter_' . $this->getName() . '_' . uniqid();
    }

    /**
     * @param QueryBuilder $queryBuilder
     */
    public function applyToQueryBuilder(QueryBuilder $queryBuilder): void
    {
        if (!$this->getFilter()->autoApply) {
            return;
        }

        $parameter = $this->getUniquePlaceholder();

        switch ($this->getComparison()) {
            case ApiTK\Filter::COMPARISON_EQUALS:
                if (strtolower($this->getValue()) === '\null'){
                    $queryBuilder->andWhere($queryBuilder->expr()->isNull($this->getQueryBuilderName($queryBuilder)));
                } else {
                    $queryBuilder->andWhere($queryBuilder->expr()->eq($this->getQueryBuilderName($queryBuilder), ':' . $parameter))
                        ->setParameter($parameter, $this->getValue());
                }
                break;

            case ApiTK\Filter::COMPARISON_NOTEQUALS:
                if (strtolower($this->getValue()) === '\null'){
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
     * @param $value
     * @return bool
     * @throws FilterException
     */
    public function matches($value): bool
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
