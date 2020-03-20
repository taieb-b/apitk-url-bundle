<?php

declare(strict_types=1);

namespace Shopping\ApiTKUrlBundle\Input;

use Doctrine\ORM\QueryBuilder;

/**
 * Interface ApplicableToQueryBuilder.
 *
 * @package Shopping\ApiTKUrlBundle\Input
 */
interface ApplicableToQueryBuilder
{
    /**
     * @param QueryBuilder $queryBuilder
     */
    public function applyToQueryBuilder(QueryBuilder $queryBuilder): void;
}
