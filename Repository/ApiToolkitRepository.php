<?php

declare(strict_types=1);

namespace Shopping\ApiTKUrlBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Shopping\ApiTKUrlBundle\Exception\PaginationException;
use Shopping\ApiTKUrlBundle\Service\ApiService;

/**
 * Class ApiToolkitRepository.
 *
 * @package Shopping\ApiTKUrlBundle\Repository
 *
 * @todo For 2.0.0, rename this to ApiToolkitServiceRepository and the other one to ApiToolkitRepository. Breaking change, but better naming...
 */
class ApiToolkitRepository extends ServiceEntityRepository
{
    /**
     * @param ApiService $apiService
     *
     * @throws NonUniqueResultException
     * @throws PaginationException
     *
     * @return array
     */
    public function findByRequest(ApiService $apiService): array
    {
        $queryBuilder = $this->createQueryBuilder('a');

        $apiService->applyToQueryBuilder($queryBuilder);

        return $queryBuilder->getQuery()->getResult();
    }
}
