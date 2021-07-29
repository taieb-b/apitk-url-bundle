<?php

declare(strict_types=1);

namespace Shopping\ApiTKUrlBundle\Annotation;

use Attribute;
use Doctrine\Common\Annotations\Annotation;
use Doctrine\Common\Annotations\Annotation\NamedArgumentConstructor;

/**
 * Class Filter.
 *
 * @package App\Dto
 * @Annotation
 * @NamedArgumentConstructor()
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class Filter implements ApiTKAttribute
{
    public const COMPARISON_EQUALS = 'eq';

    public const COMPARISON_NOTEQUALS = 'neq';

    public const COMPARISON_GREATERTHAN = 'gt';

    public const COMPARISON_GREATERTHANEQUALS = 'gteq';

    public const COMPARISON_LESSTHAN = 'lt';

    public const COMPARISON_LESSTHANEQUALS = 'lteq';

    public const COMPARISON_IN = 'in';

    public const COMPARISON_NOTIN = 'nin';

    public const COMPARISON_LIKE = 'like';

    public const AVAILABLE_COMPARISONS = [
        self::COMPARISON_EQUALS,
        self::COMPARISON_NOTEQUALS,
        self::COMPARISON_GREATERTHAN,
        self::COMPARISON_GREATERTHANEQUALS,
        self::COMPARISON_LESSTHAN,
        self::COMPARISON_LESSTHANEQUALS,
        self::COMPARISON_IN,
        self::COMPARISON_NOTIN,
        self::COMPARISON_LIKE,
    ];

    /**
     * Specify the name of this filter.
     *
     * @var string
     */
    public $name;

    /**
     * Specify what comparisons are allowed for this filter.
     *
     * @var string[]
     */
    public $allowedComparisons = self::AVAILABLE_COMPARISONS;

    /**
     * Specify what values are allowed for this filter.
     *
     * @var array
     */
    public $enum = [];

    /**
     * When the automatic ApiService->applyToQueryBuilder() method is used, it will use the "name" on the primary
     * table by default. If you need this filter to select for a different field or for a joined table, you can use this
     * option (f.e. "u.foobar").
     *
     * @var string|null
     */
    public $queryBuilderName;

    /**
     * Params will, by default, be automatically applied to the QueryBuilder with the correct
     * filter (allowedComparisons). In case you need a field with special handling, eg. a search that goes
     * across multiple fields, set this attribute to false to prevent it being added to the QueryBuilder
     * automatically.
     *
     * @var bool
     */
    public $autoApply = true;

    public function __construct(
        string $name = '',
        array $allowedComparisons = self::AVAILABLE_COMPARISONS,
        array $enum = [],
        ?string $queryBuilderName = null,
        bool $autoApply = true
    ) {
        $this->name = $name;
        $this->allowedComparisons = $allowedComparisons;
        $this->enum = $enum;
        $this->queryBuilderName = $queryBuilderName;
        $this->autoApply = $autoApply;
    }
}
