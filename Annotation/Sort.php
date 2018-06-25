<?php
namespace Ofeige\Rfc14Bundle\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * Class Sort
 * @package App\Dto
 *
 * @Annotation
 */
class Sort
{
    CONST ASCENDING = 'asc';
    CONST DESCENDING = 'desc';

    /**
     * @var string
     */
    public $name;

    /**
     * @var string[]
     */
    public $allowedDirections = ['asc', 'desc'];

    /**
     * @var string
     */
    public $queryBuilderName = null;
}