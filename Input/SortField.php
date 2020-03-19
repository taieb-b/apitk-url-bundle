<?php

namespace Shopping\ApiTKUrlBundle\Input;

use Doctrine\ORM\QueryBuilder;
use Shopping\ApiTKUrlBundle\Annotation as ApiTK;

/**
 * Class SortField.
 *
 * Represents a requested sort from the user.
 *
 * @package Shopping\ApiTKUrlBundle\Input
 */
class SortField implements ApplicableToQueryBuilder
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $direction;

    /**
     * @var ApiTK\Sort|null
     */
    private $sort;

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return SortField
     */
    public function setName(string $name): SortField
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getDirection(): string
    {
        return $this->direction;
    }

    /**
     * @param string $direction
     *
     * @return SortField
     */
    public function setDirection(string $direction): SortField
    {
        $this->direction = $direction;

        return $this;
    }

    /**
     * @return ApiTK\Sort|null
     */
    public function getSort(): ?ApiTK\Sort
    {
        return $this->sort;
    }

    /**
     * @param ApiTK\Sort|null $sort
     *
     * @return SortField
     */
    public function setSort(?ApiTK\Sort $sort): SortField
    {
        $this->sort = $sort;

        return $this;
    }

    /**
     * @param QueryBuilder $queryBuilder
     *
     * @return string
     */
    private function getQueryBuilderName(QueryBuilder $queryBuilder)
    {
        $queryBuilderName = $queryBuilder->getRootAliases()[0] . '.' . $this->getName();
        if ($this->sort->queryBuilderName) {
            $queryBuilderName = $this->sort->queryBuilderName;
        }

        return $queryBuilderName;
    }

    public function applyToQueryBuilder(QueryBuilder $queryBuilder)
    {
        if (!$this->getSort()->autoApply) {
            return;
        }

        switch ($this->getDirection()) {
            case ApiTK\Sort::ASCENDING:
                $queryBuilder->addOrderBy($this->getQueryBuilderName($queryBuilder), 'ASC');

                break;
            case ApiTK\Sort::DESCENDING:
                $queryBuilder->addOrderBy($this->getQueryBuilderName($queryBuilder), 'DESC');

                break;
        }
    }
}
