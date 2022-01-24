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
#[Attribute(Attribute::TARGET_METHOD)]
class Pagination
{
    /**
     * Maximum entries per page the client can request.
     */
    public ?int $maxEntries;

    public function __construct(
        ?int $maxEntries = null
    ) {
        $this->maxEntries = $maxEntries;
    }
}
