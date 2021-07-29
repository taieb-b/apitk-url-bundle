<?php

declare(strict_types=1);

namespace Shopping\ApiTKUrlBundle\Annotation;

use Doctrine\Common\Annotations\Annotation\NamedArgumentConstructor;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Shopping\ApiTKCommonBundle\Annotation\ParamConverter\EntityAwareAnnotationTrait;

/**
 * Class Result.
 *
 * Automatically calls the "findByRequest" method on the entity's repository and applies the given filters, sorts and
 * pagination. The result will be written in the given methods parameter.
 *
 * @example Api\Result("items", entity="App\Entity\Item")
 * @example Api\Result("users", entity="App\Entity\User", entityManager="otherConnection", methodName="findByFoobar")
 *
 * @package App\Annotation
 * @Annotation
 */
#[\Attribute(\Attribute::IS_REPEATABLE | \Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD)]
class Result extends ParamConverter
{
    use EntityAwareAnnotationTrait;
}
