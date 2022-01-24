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
class Sort
{
    public const ASCENDING = 'asc';

    public const DESCENDING = 'desc';

    public string $name;

    /**
     * Specify the allowed sorting directions.
     *
     * @var string[]
     */
    public array $allowedDirections;

    /**
     * When the automatic ApiService->applyToQueryBuilder() method is used, it will use the "name" on the primary
     * table by default. If you need this filter to select for a different field or for a joined table, you can use this
     * option (f.e. "u.foobar").
     */
    public ?string $queryBuilderName;

    /**
     * Params will, by default, be automatically applied to the QueryBuilder. In case you need a field with special
     * handling, eg. a search that goes across multiple fields, set this attribute to false to prevent it being added
     * to the QueryBuilder automatically.
     */
    public bool $autoApply;

    /**
     * @param string[] $allowedDirections
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function __construct(
        string $name,
        array $allowedDirections = [self::ASCENDING, self::DESCENDING],
        ?string $queryBuilderName = null,
        bool $autoApply = true
    ) {
        $this->name = $name;
        $this->allowedDirections = $allowedDirections;
        $this->queryBuilderName = $queryBuilderName;
        $this->autoApply = $autoApply;
    }
}
