<?php
declare(strict_types=1);

namespace Shopping\ApiFilterBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Shopping\ApiFilterBundle\Exception\PaginationException;
use Shopping\ApiFilterBundle\Service\ApiService;

/**
 * Class ApiRepository
 * @package Shopping\ApiFilterBundle\Repository
 */
class ApiRepository extends EntityRepository implements ApiRepositoryInterface
{

    /**
     * @param ApiService $apiService
     * @return array
     * @throws NonUniqueResultException
     * @throws PaginationException
     */
    public function findByRequest(ApiService $apiService): array
    {
        $queryBuilder = $this->createQueryBuilder('a');

        $apiService->applyToQueryBuilder($queryBuilder);

        return $queryBuilder->getQuery()->getResult();
    }
}