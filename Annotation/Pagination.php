<?php
namespace Ofeige\Rfc14Bundle\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * Class Pagination
 * @package App\Dto
 *
 * @Annotation
 */
class Pagination
{
    /**
     * Maximum entries per page the client can request.
     *
     * @var integer
     */
    public $maxEntries = null;
}