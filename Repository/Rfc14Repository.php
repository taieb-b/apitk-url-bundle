<?php
declare(strict_types=1);

namespace Shopping\ApiFilterBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Shopping\ApiFilterBundle\Exception\PaginationException;
use Shopping\ApiFilterBundle\Service\Rfc14Service;

/**
 * Class Rfc14Repository
 * @package Shopping\ApiFilterBundle\Repository
 */
class Rfc14Repository extends EntityRepository implements Rfc14RepositoryInterface
{

    /**
     * @param Rfc14Service $rfc14Service
     * @return array
     * @throws NonUniqueResultException
     * @throws PaginationException
     */
    public function findByRfc14(Rfc14Service $rfc14Service): array
    {
        $queryBuilder = $this->createQueryBuilder('a');

        $rfc14Service->applyToQueryBuilder($queryBuilder);

        return $queryBuilder->getQuery()->getResult();
    }
}