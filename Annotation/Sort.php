<?php

declare(strict_types=1);

namespace Shopping\ApiTKUrlBundle\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * Class Sort.
 *
 * @package App\Dto
 * @Annotation
 */
class Sort
{
    const ASCENDING = 'asc';

    const DESCENDING = 'desc';

    /**
     * Specify the name of this sort field.
     *
     * @var string
     */
    public $name;

    /**
     * Specify the allowed sorting directions.
     *
     * @var string[]
     */
    public $allowedDirections = ['asc', 'desc'];

    /**
     * When the automatic ApiService->applyToQueryBuilder() method is used, it will use the "name" on the primary
     * table by default. If you need this filter to select for a different field or for a joined table, you can use this
     * option (f.e. "u.foobar").
     *
     * @var string
     */
    public $queryBuilderName;

    /**
     * Params will, by default, be automatically applied to the QueryBuilder. In case you need a field with special
     * handling, eg. a search that goes across multiple fields, set this attribute to false to prevent it being added
     * to the QueryBuilder automatically.
     *
     * @var bool
     */
    public $autoApply = true;
}
