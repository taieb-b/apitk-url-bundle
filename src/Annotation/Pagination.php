<?php

declare(strict_types=1);

namespace Shopping\ApiTKUrlBundle\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 */
class Pagination
{
    /**
     * Maximum entries per page the client can request.
     *
     * @var int
     */
    public $maxEntries;
}
