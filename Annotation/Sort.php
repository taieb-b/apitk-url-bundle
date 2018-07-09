<?php
namespace Ofeige\Rfc14Bundle\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * Class Sort
 *
 * @package App\Dto
 * @Annotation
 */
class Sort
{
    CONST ASCENDING = 'asc';
    CONST DESCENDING = 'desc';

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
     * When the automatic Rfc14Service->applyToQueryBuilder() method is used, it will use the "name" on the primary
     * table by default. If you need this filter to select for a different field or for a joined table, you can use this
     * option (f.e. "u.foobar").
     *
     * @var string
     */
    public $queryBuilderName = null;
}