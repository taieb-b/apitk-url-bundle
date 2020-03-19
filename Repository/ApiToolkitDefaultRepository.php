<?php

declare(strict_types=1);

namespace Shopping\ApiTKUrlBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Shopping\ApiTKUrlBundle\Exception\PaginationException;
use Shopping\ApiTKUrlBundle\Service\ApiService;

/**
 * Class ApiToolkitDefaultRepository.
 *
 * @package Shopping\ApiTKUrlBundle\Repository
 */
class ApiToolkitDefaultRepository extends EntityRepository
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
