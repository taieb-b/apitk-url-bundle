<?php

namespace Shopping\ApiFilterBundle\ParamConverter;

use Shopping\ApiFilterBundle\Annotation\Result;
use Shopping\ApiFilterBundle\Repository\ApiRepositoryInterface;
use Doctrine\Common\Persistence\ManagerRegistry;
use Shopping\ApiFilterBundle\Service\ApiService;
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
     * @var ApiService
     */
    private $apiService;

    /**
     * ResultConverter constructor.
     * @param ManagerRegistry|null $registry
     * @param ApiService $apiService
     */
    public function __construct(ManagerRegistry $registry = null, ApiService $apiService)
    {
        $this->registry = $registry;
        $this->apiService = $apiService;
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
            throw new \InvalidArgumentException('You have to specify "entity" option for the ResultConverter.');
        }

        $result = $this->findInRepository($options['entity'], $options['entityManager'] ?? null, $options['methodName'] ?? null);

        $request->attributes->set($configuration->getName(), $result);

        return true;
    }

    /**
     * @param string $entity
     * @param string|null $manager
     * @param string|null $methodName
     * @return array
     */
    private function findInRepository(string $entity, string $manager = null, string $methodName = null)
    {
        $om = $this->getManager($manager, $entity);
        $repository = $om->getRepository($entity);
        
        if (!$repository instanceof ApiRepositoryInterface) {
            throw new \InvalidArgumentException(sprintf('Repository for entity "%s" does not implement the ApiRepositoryInterface.', $entity));
        }

        if ($methodName === null) {
            $methodName = 'findByRequest';
        }

        return $repository->$methodName($this->apiService);
    }

    /**
     * @param string|null $name
     * @param string $entity
     * @return \Doctrine\Common\Persistence\ObjectManager|null
     */
    private function getManager(?string $name, string $entity)
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
        return ($configuration instanceof ParamConverter && $configuration->getClass() === 'api.result')
            || $configuration instanceof Result;
    }
}