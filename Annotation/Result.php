<?php

namespace Shopping\ApiFilterBundle\Annotation;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

/**
 * Class Result
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
class Result extends ParamConverter
{
    /**
     * @param $entityName
     */
    public function setEntity($entityName)
    {
        $options = $this->getOptions();
        $options['entity'] = $entityName;

        $this->setOptions($options);
    }

    /**
     * @param $manager
     */
    public function setEntityManager($manager)
    {
        $options = $this->getOptions();
        $options['entityManager'] = $manager;

        $this->setOptions($options);
    }

    /**
     * @param $methodName
     */
    public function setMethodName($methodName)
    {
        $options = $this->getOptions();
        $options['methodName'] = $methodName;

        $this->setOptions($options);
    }
}