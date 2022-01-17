<?php

declare(strict_types=1);

namespace Shopping\ApiTKUrlBundle\Input;

use Doctrine\ORM\QueryBuilder;

interface ApplicableToQueryBuilder
{
    public function applyToQueryBuilder(QueryBuilder $queryBuilder): void;
}
