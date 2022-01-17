<?php

declare(strict_types=1);

namespace Shopping\ApiTKUrlBundle\Input;

use Doctrine\ORM\QueryBuilder;
use Shopping\ApiTKUrlBundle\Annotation as ApiTK;

/**
 * Represents a requested sort from the user.
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

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): SortField
    {
        $this->name = $name;

        return $this;
    }

    public function getDirection(): string
    {
        return $this->direction;
    }

    public function setDirection(string $direction): SortField
    {
        $this->direction = $direction;

        return $this;
    }

    public function getSort(): ?ApiTK\Sort
    {
        return $this->sort;
    }

    public function setSort(?ApiTK\Sort $sort): SortField
    {
        $this->sort = $sort;

        return $this;
    }

    private function getQueryBuilderName(QueryBuilder $queryBuilder): string
    {
        $queryBuilderName = $queryBuilder->getRootAliases()[0] . '.' . $this->getName();
        if ($this->sort !== null && $this->sort->queryBuilderName) {
            $queryBuilderName = $this->sort->queryBuilderName;
        }

        return $queryBuilderName;
    }

    public function applyToQueryBuilder(QueryBuilder $queryBuilder): void
    {
        if ($this->sort !== null && !$this->sort->autoApply) {
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
