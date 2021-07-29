<?php

declare(strict_types=1);

namespace Shopping\ApiTKUrlBundle\Annotation;

use Attribute;
use Doctrine\Common\Annotations\Annotation;
use Doctrine\Common\Annotations\Annotation\NamedArgumentConstructor;

/**
 * Class Sort.
 *
 * @package App\Dto
 * @Annotation
 * @NamedArgumentConstructor()
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class Sort implements ApiTKAttribute
{
    public const ASCENDING = 'asc';

    public const DESCENDING = 'desc';

    public const AVAILABLE_DIRECTIONS = ['asc', 'desc'];

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
    public $allowedDirections = self::AVAILABLE_DIRECTIONS;

    /**
     * When the automatic ApiService->applyToQueryBuilder() method is used, it will use the "name" on the primary
     * table by default. If you need this filter to select for a different field or for a joined table, you can use this
     * option (f.e. "u.foobar").
     *
     * @var string|null
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

    public function __construct(
        string $name = '',
        array $allowedDirections = self::AVAILABLE_DIRECTIONS,
        ?string $queryBuilderName = null,
        bool $autoApply = true
    ) {
        $this->name = $name;
        $this->allowedDirections = $allowedDirections;
        $this->queryBuilderName = $queryBuilderName;
        $this->autoApply = $autoApply;
    }
}
