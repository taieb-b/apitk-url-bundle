<?php

declare(strict_types=1);

namespace Shopping\ApiTKUrlBundle\Service;

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\QueryBuilder;
use Shopping\ApiTKCommonBundle\Exception\MissingDependencyException;
use Shopping\ApiTKHeaderBundle\Service\HeaderInformation;
use Shopping\ApiTKUrlBundle\Annotation as Api;
use Shopping\ApiTKUrlBundle\Exception\PaginationException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Pagination specific methods for the ApiService.
 */
trait PaginationTrait
{
    private RequestStack $requestStack;

    private HeaderInformation $headerInformation;

    private ?Api\Pagination $pagination = null;

    private ?int $paginationOffset = null;

    private ?int $paginationLimit = null;

    private int $paginationTotal;

    /**
     * Checks if only allowed sort fields were given in the request. Will be called by the event listener.
     */
    public function handleIsPaginatable(Api\Pagination $pagination): void
    {
        $this->pagination = $pagination;
    }

    /**
     * Applies the requested pagination to the query builder.
     *
     * @throws PaginationException
     * @throws NonUniqueResultException
     * @throws MissingDependencyException
     */
    public function applyPaginationToQueryBuilder(QueryBuilder $queryBuilder): void
    {
        if (!class_exists(QueryBuilder::class)) {
            throw new MissingDependencyException(
                'You need to install doctrine/orm and doctrine/doctrine-bundle > 2.0 to use ORM-capabilities within ApiTK bundles.'
            );
        }

        if ($this->pagination !== null) {
            $queryBuilder->distinct();

            $totalQueryBuilder = clone $queryBuilder;
            $totalQueryBuilder->select('COUNT(DISTINCT ' . $totalQueryBuilder->getRootAliases()[0] . ')');

            try {
                $this->setPaginationTotal((int) $totalQueryBuilder->getQuery()->getSingleScalarResult());
            } catch (\Exception $e) {
                // f.e. for TableNotFoundExceptions
            }

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
        $request = $this->requestStack->getMainRequest() ?? Request::createFromGlobals();
        $parameter = $request->query->get('limit', null);

        if ($parameter !== null) {
            if ($this->pagination === null) {
                throw new PaginationException('Limit parameter not available in current request.');
            }

            $parts = explode(',', (string) $parameter);
            if (count($parts) === 1) {
                $this->paginationLimit = (int) $parts[0];
            } elseif (count($parts) === 2) {
                $this->paginationOffset = (int) $parts[0];
                $this->paginationLimit = (int) $parts[1];
            } else {
                throw new PaginationException('Invalid limit parameter. Allowed formats: limit=[limit], limit=[offset],[limit]');
            }
        } elseif ($this->pagination !== null) {
            $this->paginationLimit = $this->pagination->maxEntries;
        }
    }

    /**
     * Gets the pagination offset.
     *
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
     * @throws PaginationException
     */
    public function getPaginationLimit(): ?int
    {
        $this->parsePagination();

        return $this->paginationLimit ?? ($this->pagination !== null ? $this->pagination->maxEntries : null);
    }

    /**
     * Sets the total amount of rows for the given filters/sorts.
     *
     * Only has to be set if applyToQueryBuilder() is not used.
     */
    public function setPaginationTotal(int $paginationTotal): void
    {
        $this->paginationTotal = $paginationTotal;

        $this->headerInformation->add('pagination-total', (string) $this->paginationTotal);
    }

    /**
     * Returns the total amount of rows for the given filters/sorts.
     */
    public function getPaginationTotal(): ?int
    {
        return $this->paginationTotal;
    }
}
