<?php

declare(strict_types=1);

namespace Shopping\ApiTKUrlBundle\Annotation;

use Attribute;
use Doctrine\Common\Annotations\Annotation;
use Doctrine\Common\Annotations\Annotation\NamedArgumentConstructor;

/**
 * @Annotation
 * @NamedArgumentConstructor()
 */
#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_METHOD)]
class Filter
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

    private const ALL_COMPARISONS = [
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
     */
    public string $name;

    /**
     * Specify what comparisons are allowed for this filter.
     *
     * @var string[]
     */
    public array $allowedComparisons;

    /**
     * Specify what values are allowed for this filter.
     *
     * @var mixed[]
     */
    public array $enum = [];

    /**
     * When the automatic ApiService->applyToQueryBuilder() method is used, it will use the "name" on the primary
     * table by default. If you need this filter to select for a different field or for a joined table, you can use this
     * option (f.e. "u.foobar").
     */
    public ?string $queryBuilderName;

    /**
     * Params will, by default, be automatically applied to the QueryBuilder with the correct
     * filter (allowedComparisons). In case you need a field with special handling, eg. a search that goes
     * across multiple fields, set this attribute to false to prevent it being added to the QueryBuilder
     * automatically.
     */
    public bool $autoApply = true;

    /**
     * @param string[] $allowedComparisons
     * @param mixed[]  $enum
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function __construct(
        string $name,
        array $allowedComparisons = self::ALL_COMPARISONS,
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
