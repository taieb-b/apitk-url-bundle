<?php
namespace Shopping\ApiFilterBundle\Input;

use Shopping\ApiFilterBundle\Annotation as Api;
use Doctrine\ORM\QueryBuilder;

/**
 * Class SortField
 *
 * Represents a requested sort from the user.
 *
 * @package Shopping\ApiFilterBundle\Input
 */
class SortField implements ApplyableToQueryBuilder
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
     * @var Api\Sort|null
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
     * @return SortField
     */
    public function setDirection(string $direction): SortField
    {
        $this->direction = $direction;
        return $this;
    }

    /**
     * @return Api\Sort|null
     */
    public function getSort(): ?Api\Sort
    {
        return $this->sort;
    }

    /**
     * @param Api\Sort|null $sort
     * @return SortField
     */
    public function setSort(?Api\Sort $sort): SortField
    {
        $this->sort = $sort;
        return $this;
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @return string
     */
    private function getQueryBuilderName(QueryBuilder $queryBuilder) {
        $queryBuilderName = $queryBuilder->getRootAliases()[0] . '.' . $this->getName();
        if ($this->sort->queryBuilderName) {
            $queryBuilderName = $this->sort->queryBuilderName;
        }

        return $queryBuilderName;
    }

    public function applyToQueryBuilder(QueryBuilder $queryBuilder)
    {
        switch ($this->getDirection()) {
            case Api\Sort::ASCENDING:
                $queryBuilder->addOrderBy($this->getQueryBuilderName($queryBuilder), 'ASC');
                break;

            case Api\Sort::DESCENDING:
                $queryBuilder->addOrderBy($this->getQueryBuilderName($queryBuilder), 'DESC');
                break;
        }
    }
}