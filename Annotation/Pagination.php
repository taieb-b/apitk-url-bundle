<?php

declare(strict_types=1);

namespace Shopping\ApiTKUrlBundle\Annotation;

use Attribute;
use Doctrine\Common\Annotations\Annotation;
use Doctrine\Common\Annotations\Annotation\NamedArgumentConstructor;

/**
 * Class Pagination.
 *
 * @package App\Dto
 * @Annotation
 * @NamedArgumentConstructor()
 */
#[Attribute(Attribute::TARGET_METHOD)]
class Pagination implements ApiTKAttribute
{
    /**
     * Maximum entries per page the client can request.
     *
     * @var int|null
     */
    public $maxEntries;

    public function __construct(?int $maxEntries = null)
    {
        $this->maxEntries = $maxEntries;
    }
}
