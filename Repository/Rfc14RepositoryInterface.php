<?php

namespace Ofeige\Rfc14Bundle\Repository;

use Ofeige\Rfc14Bundle\Service\Rfc14Service;

/**
 * Interface Rfc14RepositoryInterface
 * @package Ofeige\Rfc14Bundle\Repository
 */
interface Rfc14RepositoryInterface
{
    /**
     * @param Rfc14Service $rfc14Service
     * @return array
     */
    public function findByRfc14(Rfc14Service $rfc14Service): array;
}