<?php

namespace Ofeige\Rfc14Bundle\Annotation;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

/**
 * Class Result
 * @package App\Annotation
 *
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
        $options['entity_manager'] = $manager;

        $this->setOptions($options);
    }
}