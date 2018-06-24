<?php

namespace Bywulf\Rfc14Bundle\Service;

use Bywulf\Rfc14Bundle\Annotation AS Rfc14;
use Bywulf\Rfc14Bundle\Input\SortField;
use Bywulf\Rfc14Bundle\Exception\SortException;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\RequestStack;

class SortFromRequestQuery implements Sort {

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var SortField[]
     */
    private $sortFields;

    /**
     * @var Rfc14\Sort[]
     */
    private $sorts;

    /**
     * SortFromRequestQuery constructor.
     * @param RequestStack $requestStack
     */
    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    /**
     * Checks if only allowed sort fields were given in the request;
     *
     * @param Rfc14\Sort[] $sorts
     * @throws \Exception
     */
    public function handleAllowed(array $sorts): void
    {
        $this->sorts = $sorts;

        foreach ($this->getAll() as $sortField) {
            if (!$this->isAllowedField($sortField)) {
                throw new SortException(
                    sprintf(
                        'Sort "%s" with direction "%s" is not allowed in this request. Available sorts: %s',
                        $sortField->getName(),
                        $sortField->getComparison(),
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
    private function isAllowedField(SortField $sortField): bool
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
        foreach ($this->requestStack->getMasterRequest()->query->get('sort') as $name => $direction) {
            $sortField = new SortField();
            $sortField->setName($name)
                ->setDirection($direction)
                ->setSort($this->getSortByName($name));
        }
    }

    /**
     * @return SortField[]
     */
    public function getAll(): array
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
    public function has(string $name): bool
    {
        return $this->get($name) !== null;
    }

    /**
     * Returns the sort field for the given name.
     *
     * @param string $name
     * @return SortField|null
     */
    public function get(string $name): ?SortField
    {
        foreach ($this->getAll() as $sortField) {
            if ($sortField->getName() === $name) {
                return $sortField;
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
        foreach ($this->getAll() as $sortField) {
            $sortField->applyToQueryBuilder($queryBuilder);
        }
    }
}