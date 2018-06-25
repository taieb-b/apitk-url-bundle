<?php

namespace Ofeige\Rfc14Bundle\Service;

use Ofeige\Rfc14Bundle\Exception\PaginationException;
use Doctrine\ORM\QueryBuilder;
use Ofeige\Rfc14Bundle\Annotation AS Rfc14;
use Symfony\Component\HttpFoundation\RequestStack;

class PaginationFromRequestQuery implements Pagination {
    /**
     * @var Rfc14\Pagination
     */
    private $pagination;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var int|null
     */
    private $offset;

    /**
     * @var int|null
     */
    private $limit;

    /**
     * PaginationFromRequestQuery constructor.
     * @param RequestStack $requestStack
     */
    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

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
                $this->limit = (int) $parts[0];
            } elseif (count($parts) === 2) {
                $this->offset = (int) $parts[0];
                $this->limit = (int) $parts[1];
            } else {
                throw new PaginationException('Invalid limit parameter. Allowed formats: limit=[limit], limit=[offset],[limit]');
            }
        }
    }

    /**
     * @return int
     * @throws PaginationException
     */
    public function getOffset(): int
    {
        $this->parsePagination();

        return $this->offset ?? 0;
    }

    /**
     * @return int|null
     * @throws PaginationException
     */
    public function getLimit(): ?int
    {
        $this->parsePagination();

        return $this->limit;
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @throws PaginationException
     */
    public function applyToQueryBuilder(QueryBuilder $queryBuilder): void
    {
        $queryBuilder->setMaxResults($this->getLimit());
        $queryBuilder->setFirstResult($this->getOffset());
    }
}