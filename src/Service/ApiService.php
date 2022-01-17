<?php

declare(strict_types=1);

namespace Shopping\ApiTKUrlBundle\Service;

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\QueryBuilder;
use Shopping\ApiTKCommonBundle\Exception\MissingDependencyException;
use Shopping\ApiTKHeaderBundle\Service\HeaderInformation;
use Shopping\ApiTKUrlBundle\Exception\PaginationException;
use Symfony\Component\HttpFoundation\RequestStack;

class ApiService implements Filter, Pagination, Sort
{
    use FilterTrait;
    use SortTrait;
    use PaginationTrait;

    public function __construct(
        private RequestStack $requestStack,
        private HeaderInformation $headerInformation
    ) {
    }

    /**
     * @throws MissingDependencyException
     * @throws NonUniqueResultException
     * @throws PaginationException
     */
    public function applyToQueryBuilder(QueryBuilder $queryBuilder): void
    {
        if (!class_exists(QueryBuilder::class)) {
            throw new MissingDependencyException(
                'You need to install doctrine/orm and doctrine/doctrine-bundle > 2.0 to use ORM-capabilities within ApiTK bundles.'
            );
        }

        $this->applyFilteredFieldsToQueryBuilder($queryBuilder);
        $this->applySortedFieldsToQueryBuilder($queryBuilder);
        $this->applyPaginationToQueryBuilder($queryBuilder);
    }
}
