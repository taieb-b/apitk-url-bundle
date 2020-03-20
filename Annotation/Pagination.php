<?php

declare(strict_types=1);

namespace Shopping\ApiTKUrlBundle\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * Class Pagination.
 *
 * @package App\Dto
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
