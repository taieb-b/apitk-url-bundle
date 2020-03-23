<?php

declare(strict_types=1);

namespace Shopping\ApiTKUrlBundle\ParamConverter;

use Doctrine\Common\Persistence\ManagerRegistry;
use InvalidArgumentException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Shopping\ApiTKCommonBundle\ParamConverter\ContextAwareParamConverterTrait;
use Shopping\ApiTKCommonBundle\ParamConverter\EntityAwareParamConverterTrait;
use Shopping\ApiTKUrlBundle\Annotation\Result;
use Shopping\ApiTKUrlBundle\Service\ApiService;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ResultConverter.
 *
 * Fetches the filtered, sorted and paginated result from the configured repository and hands it over to the controller
 * action.
 *
 * @package Shopping\ApiTKUrlBundle\ParamConverter
 */
class ResultConverter implements ParamConverterInterface
{
    use ContextAwareParamConverterTrait;
    use EntityAwareParamConverterTrait;

    /**
     * @var ApiService
     */
    private $apiService;

    /**
     * ResultConverter constructor.
     *
     * @param ManagerRegistry|null $registry
     * @param ApiService           $apiService
     */
    public function __construct(?ManagerRegistry $registry, ApiService $apiService)
    {
        $this->registry = $registry;
        $this->apiService = $apiService;
    }

    /**
     * Stores the object in the request.
     *
     * @param Request        $request
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
     * @param ParamConverter $configuration
     *
     * @return bool True if the object is supported, else false
     */
    public function supports(ParamConverter $configuration): bool
    {
        return ($configuration instanceof ParamConverter && $configuration->getClass() === 'api.result')
            || $configuration instanceof Result;
    }
}
