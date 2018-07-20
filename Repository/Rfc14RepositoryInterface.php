<?php

namespace Shopping\ApiFilterBundle\Repository;

use Shopping\ApiFilterBundle\Service\Rfc14Service;

/**
 * Interface Rfc14RepositoryInterface
 * @package Shopping\ApiFilterBundle\Repository
 */
interface Rfc14RepositoryInterface
{
    /**
     * @param Rfc14Service $rfc14Service
     * @return array
     */
    public function findByRfc14(Rfc14Service $rfc14Service): array;
}