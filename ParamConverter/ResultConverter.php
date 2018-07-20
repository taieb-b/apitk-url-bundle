<?php

namespace Shopping\ApiFilterBundle\ParamConverter;

use Shopping\ApiFilterBundle\Annotation\Result;
use Shopping\ApiFilterBundle\Repository\Rfc14RepositoryInterface;
use Doctrine\Common\Persistence\ManagerRegistry;
use Shopping\ApiFilterBundle\Service\Rfc14Service;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ResultConverter
 *
 * Fetches the filtered, sorted and paginated result from the configured repository and hands it over to the controller
 * action.
 *
 * @package Shopping\ApiFilterBundle\ParamConverter
 */
class ResultConverter implements ParamConverterInterface
{
    /**
     * @var ManagerRegistry
     */
    private $registry;
    /**
     * @var Rfc14Service
     */
    private $rfc14Service;

    /**
     * Rfc14ParamConverter constructor.
     * @param ManagerRegistry|null $registry
     * @param Rfc14Service $rfc14Service
     */
    public function __construct(ManagerRegistry $registry = null, Rfc14Service $rfc14Service)
    {
        $this->registry = $registry;
        $this->rfc14Service = $rfc14Service;
    }

    /**
     * Stores the object in the request.
     *
     * @param Request $request
     * @param ParamConverter $configuration Contains the name, class and options of the object
     *
     * @return bool True if the object has been successfully set, else false
     */
    public function apply(Request $request, ParamConverter $configuration)
    {
        $options = $configuration->getOptions();

        if (!isset($options['entity'])) {
            throw new \InvalidArgumentException('You have to specify "entity" option for the Rfc14ParamConverter.');
        }

        $result = $this->findByRfc14($options['entity'], $options['entityManager'] ?? null);

        $request->attributes->set($configuration->getName(), $result);

        return true;
    }

    /**
     * @param string $entity
     * @param string|null $manager
     * @return array
     */
    private function findByRfc14($entity, $manager = null)
    {
        $om = $this->getManager($manager, $entity);
        $repository = $om->getRepository($entity);
        
        if (!$repository instanceof Rfc14RepositoryInterface) {
            throw new \InvalidArgumentException(sprintf('Repository for entity "%s" does not implement the Rfc14RepositoryInterface.', $entity));
        }

        return $repository->findByRfc14($this->rfc14Service);
    }

    /**
     * @param string|null $name
     * @param string|null $entity
     * @return \Doctrine\Common\Persistence\ObjectManager|null
     */
    private function getManager(?string $name, ?string $entity)
    {
        if (null === $name) {
            return $this->registry->getManagerForClass($entity);
        }

        return $this->registry->getManager($name);
    }

    /**
     * Checks if the object is supported.
     *
     * @param ParamConverter $configuration
     * @return bool True if the object is supported, else false
     */
    public function supports(ParamConverter $configuration)
    {
        return ($configuration instanceof ParamConverter && $configuration->getClass() === 'rfc14.result')
            || $configuration instanceof Result;
    }
}