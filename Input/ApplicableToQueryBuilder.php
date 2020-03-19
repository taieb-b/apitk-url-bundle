<?php

namespace Shopping\ApiTKUrlBundle\Input;

use Doctrine\ORM\QueryBuilder;

interface ApplicableToQueryBuilder
{
    public function applyToQueryBuilder(QueryBuilder $queryBuilder);
}
