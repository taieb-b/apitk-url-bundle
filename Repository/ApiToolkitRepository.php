<?php
declare(strict_types=1);

namespace Shopping\ApiTKUrlBundle\Repository;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use LogicException;
use Shopping\ApiTKUrlBundle\Exception\PaginationException;
use Shopping\ApiTKUrlBundle\Service\ApiService;

/**
 * Class ApiRepository
 * @package Shopping\ApiTKUrlBundle\Repository
 */
class ApiToolkitRepository extends EntityRepository
{

    /**
     * @param ManagerRegistry $registry
     * @param string $entityClass The class name of the entity this repository manages
     */
    public function __construct(ManagerRegistry $registry, $entityClass)
    {
        /** @var EntityManager $manager */
        $manager = $registry->getManagerForClass($entityClass);

        if ($manager === null) {
            throw new LogicException(sprintf(
                'Could not find the entity manager for class "%s". Check your Doctrine configuration to make sure it is configured to load this entity’s metadata.',
                $entityClass
            ));
        }

        parent::__construct($manager, $manager->getClassMetadata($entityClass));
    }

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