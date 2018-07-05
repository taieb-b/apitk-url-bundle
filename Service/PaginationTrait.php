<?php
declare(strict_types=1);

namespace Ofeige\Rfc14Bundle\Service;

use Doctrine\ORM\QueryBuilder;
use Ofeige\Rfc14Bundle\Exception\PaginationException;
use Symfony\Component\HttpFoundation\RequestStack;
use Ofeige\Rfc14Bundle\Annotation as Rfc14;

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
     * Checks if only allowed sort fields were given in the request;
     *
     * @param Rfc14\Pagination $pagination
     */
    public function handleIsPaginatable(Rfc14\Pagination $pagination): void
    {
        $this->pagination = $pagination;
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @throws PaginationException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function applyPaginationToQueryBuilder(QueryBuilder $queryBuilder): void
    {
        if ($this->pagination !== null) {
            $totalQueryBuilder = clone $queryBuilder;
            $totalQueryBuilder->select('COUNT(DISTINCT ' . $totalQueryBuilder->getRootAliases()[0] . ')');
            $this->addHeaderInformation('pagination-total', (int)$totalQueryBuilder->getQuery()->getSingleScalarResult());

            $queryBuilder->setMaxResults($this->getPaginationLimit());
            $queryBuilder->setFirstResult($this->getPaginationOffset());
        }
    }

    /**
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
     * @return int
     * @throws PaginationException
     */
    public function getPaginationOffset(): int
    {
        $this->parsePagination();

        return $this->paginationOffset ?? 0;
    }

    /**
     * @return int|null
     * @throws PaginationException
     */
    public function getPaginationLimit(): ?int
    {
        $this->parsePagination();

        return $this->paginationLimit ?? $this->pagination->maxEntries;
    }

    /**
     * Only has to be set if applyToQueryBuilder() is not used.
     *
     * @param int $paginationTotal
     */
    public function setPaginationTotal(int $paginationTotal): void
    {
        $this->paginationTotal = $paginationTotal;
    }

    /**
     * @return int|null
     */
    public function getPaginationTotal(): ?int
    {
        return $this->paginationTotal;
    }
}