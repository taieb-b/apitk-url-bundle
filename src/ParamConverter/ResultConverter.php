<?php

declare(strict_types=1);

namespace Shopping\ApiTKUrlBundle\ParamConverter;

use Doctrine\Persistence\ManagerRegistry;
use InvalidArgumentException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Shopping\ApiTKCommonBundle\ParamConverter\ContextAwareParamConverterTrait;
use Shopping\ApiTKCommonBundle\ParamConverter\EntityAwareParamConverterTrait;
use Shopping\ApiTKUrlBundle\Annotation\Result;
use Shopping\ApiTKUrlBundle\Service\ApiService;
use Symfony\Component\HttpFoundation\Request;

/**
 * Fetches the filtered, sorted and paginated result from the configured repository and hands it over to the controller
 * action.
 */
class ResultConverter implements ParamConverterInterface
{
    use ContextAwareParamConverterTrait;
    use EntityAwareParamConverterTrait;

    public function __construct(
        ?ManagerRegistry $registry,
        private ApiService $apiService
    ) {
        $this->registry = $registry;
    }

    /**
     * Stores the object in the request.
     *
     * @param ParamConverter $configuration Contains the name, class and options of the object
     *
     * @return bool True if the object has been successfully set, else false
     */
    public function apply(Request $request, ParamConverter $configuration)
    {
        $this->initialize($request, $configuration);

        if ($this->getEntity() === null) {
            throw new InvalidArgumentException('You have to specify "entity" option for the ResultConverter.');
        }

        $methodName = $this->getRepositoryMethodName('findByRequest');
        $result = $this->callRepositoryMethod($methodName ?? 'findByRequest', $this->apiService);

        $request->attributes->set($configuration->getName(), $result);

        return true;
    }

    /**
     * Checks if the object is supported.
     *
     * @return bool True if the object is supported, else false
     */
    public function supports(ParamConverter $configuration): bool
    {
        return ($configuration instanceof ParamConverter && $configuration->getClass() === 'api.result')
            || $configuration instanceof Result;
    }
}
