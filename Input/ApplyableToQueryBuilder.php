<?php

namespace Bywulf\Rfc14Bundle\Input;

use Doctrine\ORM\QueryBuilder;

interface ApplyableToQueryBuilder {
    public function applyToQueryBuilder(QueryBuilder $queryBuilder);
}