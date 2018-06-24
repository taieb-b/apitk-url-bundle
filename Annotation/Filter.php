<?php
namespace Bywulf\Rfc14Bundle\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * Class Filter
 * @package App\Dto
 *
 * @Annotation
 */
class Filter
{
    const COMPARISON_EQUALS = 'eq';
    const COMPARISON_NOTEQUALS = 'neq';
    const COMPARISON_GREATERTHAN = 'gt';
    const COMPARISON_GREATERTHANEQUALS = 'gteq';
    const COMPARISON_LESSTHAN = 'lt';
    const COMPARISON_LESSTHANEQUALS = 'lteq';
    const COMPARISON_IN = 'in';
    const COMPARISON_NOTIN = 'nin';

    /**
     * @var string
     */
    public $name;

    /**
     * @var string[]
     */
    public $allowedComparisons = ['eq','neq','gt','gteq','lt','lteq','in','nin'];

    /**
     * @var string
     */
    public $queryBuilderName = null;
}