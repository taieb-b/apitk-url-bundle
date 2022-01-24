<?php

declare(strict_types=1);

namespace Shopping\ApiTKUrlBundle\Annotation;

use Attribute;
use Doctrine\Common\Annotations\Annotation\NamedArgumentConstructor;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

/**
 * Automatically calls the "findByRequest" method on the entity's repository and applies the given filters, sorts and
 * pagination. The result will be written in the given methods parameter.
 *
 * @example Api\Result("items", entity: "App\Entity\Item")
 * @example Api\Result("users", entity: "App\Entity\User", entityManager: "otherConnection", methodName: "findByFoobar")
 *
 * @Annotation
 * @NamedArgumentConstructor()
 */
#[Attribute(Attribute::TARGET_METHOD)]
class Result extends ParamConverter
{
    public function __construct(
        string $name,
        string $entity,
        ?string $entityManager = null,
        ?string $methodName = null,
    ) {
        $options = [
            'entity' => $entity,
        ];

        if ($entityManager !== null) {
            $options['entityManager'] = $entityManager;
        }

        if ($methodName !== null) {
            $options['methodName'] = $methodName;
        }

        parent::__construct($name, null, $options);
    }
}
