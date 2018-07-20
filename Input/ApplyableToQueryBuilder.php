<?php

namespace Shopping\ApiFilterBundle\Input;

use Doctrine\ORM\QueryBuilder;

interface ApplyableToQueryBuilder {
    public function applyToQueryBuilder(QueryBuilder $queryBuilder);
}