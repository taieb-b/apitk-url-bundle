<?php

namespace Shopping\ApiFilterBundle\Annotation;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

/**
 * Class Result
 *
 * Automatically calls the "findByRfc14" method on the entity's repository and applies the given filters, sorts and
 * pagination. The result will be written in the given methods parameter.
 *
 * @example Rfc14\Result("items", entity="App\Entity\Item")
 * @example Rfc14\Result("users", entity="App\Entity\User", entityManager="otherConnection")
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
}