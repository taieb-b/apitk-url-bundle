<?php

namespace Shopping\ApiTKUrlBundle\Input;

use Doctrine\ORM\QueryBuilder;

interface ApplyableToQueryBuilder {
    public function applyToQueryBuilder(QueryBuilder $queryBuilder);
}