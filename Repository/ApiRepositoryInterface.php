<?php

namespace Shopping\ApiFilterBundle\Repository;

use Shopping\ApiFilterBundle\Service\ApiService;

/**
 * Interface ApiRepositoryInterface
 * @package Shopping\ApiFilterBundle\Repository
 */
interface ApiRepositoryInterface
{
    /**
     * @param ApiService $apiService
     * @return array
     */
    public function findByRequest(ApiService $apiService): array;
}