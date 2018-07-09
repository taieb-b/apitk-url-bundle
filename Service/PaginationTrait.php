<?php
declare(strict_types=1);

namespace Ofeige\Rfc14Bundle\Service;

use Doctrine\ORM\QueryBuilder;
use Ofeige\Rfc14Bundle\Exception\PaginationException;
use Symfony\Component\HttpFoundation\RequestStack;
use Ofeige\Rfc14Bundle\Annotation as Rfc14;

/**
 * Trait PaginationTrait
 *
 * Pagination specific methods for the Rfc14Service.
 *
 * @package Ofeige\Rfc14Bundle\Service
 */
trait PaginationTrait
{
    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var Rfc14\Pagination
     */
    private $pagination;

    /**
     * @var int|null
     */
    private $paginationOffset;

    /**
     * @var int|null
     */
    private $paginationLimit;

    /**
     * @var int
     */
    private $paginationTotal;

    /**
     * Checks if only allowed sort fields were given in the request. Will be called by the event listener.
     *
     * @param Rfc14\Pagination $pagination
     */
    public function handleIsPaginatable(Rfc14\Pagination $pagination): void
    {
        $this->pagination = $pagination;
    }

    /**
     * Applies the requested pagination to the query builder.
     *
     * @param QueryBuilder $queryBuilder
     * @throws PaginationException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function applyPaginationToQueryBuilder(QueryBuilder $queryBuilder): void
    {
        if ($this->pagination !== null) {
            $totalQueryBuilder = clone $queryBuilder;
            $totalQueryBuilder->select('COUNT(DISTINCT ' . $totalQueryBuilder->getRootAliases()[0] . ')');

            try {
                $this->setPaginationTotal((int)$totalQueryBuilder->getQuery()->getSingleScalarResult());
            } catch (\Exception $e) {} //F.e. for TableNotFoundExceptions

            $queryBuilder->setMaxResults($this->getPaginationLimit());
            $queryBuilder->setFirstResult($this->getPaginationOffset());
        }
    }

    /**
     * Reads the requested pagination from the query part of the url and stores it for later use.
     *
     * @throws PaginationException
     */
    private function parsePagination(): void
    {
        $parameter = $this->requestStack->getMasterRequest()->query->get('limit');

        if ($parameter !== null) {
            if ($this->pagination === null) {
                throw new PaginationException('Limit parameter not available in current request.');
            }

            $parts = explode(',', $parameter);
            if (count($parts) === 1) {
                $this->paginationLimit = (int) $parts[0];
            } elseif (count($parts) === 2) {
                $this->paginationOffset = (int) $parts[0];
                $this->paginationLimit = (int) $parts[1];
            } else {
                throw new PaginationException('Invalid limit parameter. Allowed formats: limit=[limit], limit=[offset],[limit]');
            }
        } else if ($this->pagination !== null) {
            $this->paginationLimit = $this->pagination->maxEntries;
        }
    }

    /**
     * Gets the pagination offset.
     *
     * @return int
     * @throws PaginationException
     */
    public function getPaginationOffset(): int
    {
        $this->parsePagination();

        return $this->paginationOffset ?? 0;
    }

    /**
     * Gets the pagination limit (max results).
     *
     * @return int|null
     * @throws PaginationException
     */
    public function getPaginationLimit(): ?int
    {
        $this->parsePagination();

        return $this->paginationLimit ?? $this->pagination->maxEntries;
    }

    /**
     * Sets the total amount of rows for the given filters/sorts.
     *
     * Only has to be set if applyToQueryBuilder() is not used.
     *
     * @param int $paginationTotal
     */
    public function setPaginationTotal(int $paginationTotal): void
    {
        $this->paginationTotal = $paginationTotal;

        $this->addHeaderInformation('pagination-total', $this->paginationTotal);
    }

    /**
     * Returns the total amount of rows for the given filters/sorts.
     *
     * @return int|null
     */
    public function getPaginationTotal(): ?int
    {
        return $this->paginationTotal;
    }
}