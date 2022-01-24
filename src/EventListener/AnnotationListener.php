<?php

declare(strict_types=1);

namespace Shopping\ApiTKUrlBundle\EventListener;

use Doctrine\Common\Annotations\Reader;
use ReflectionAttribute;
use ReflectionException;
use ReflectionMethod;
use ReflectionObject;
use Shopping\ApiTKUrlBundle\Annotation as Api;
use Shopping\ApiTKUrlBundle\Exception\FilterException;
use Shopping\ApiTKUrlBundle\Exception\SortException;
use Shopping\ApiTKUrlBundle\Service\ApiService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Event\ControllerEvent;

/**
 * Reads the filter/sort/pagination annotations and stores them in the ApiService.
 */
class AnnotationListener
{
    private bool $mainRequest = true;

    public function __construct(
        private Reader $reader,
        private ApiService $apiService
    ) {
    }

    /**
     * @throws FilterException
     * @throws SortException
     * @throws ReflectionException
     */
    public function onKernelController(ControllerEvent $event): void
    {
        // Only parse annotations on original action
        if (!$this->mainRequest) {
            return;
        }
        $this->mainRequest = false;

        $controller = $event->getController();

        if (!is_array($controller)) {
            return;
        }

        // Filters
        $this->apiService->handleAllowedFilters($this->getAnnotationsByController($controller, Api\Filter::class));

        // Sorts
        $this->apiService->handleAllowedSorts($this->getAnnotationsByController($controller, Api\Sort::class));

        // Pagination
        /** @var Api\Pagination[] $paginations */
        $paginations = $this->getAnnotationsByController($controller, Api\Pagination::class);
        if (count($paginations) > 0) {
            $pagination = reset($paginations);
            if ($pagination !== false) {
                $this->apiService->handleIsPaginatable($pagination);
            }
        }
    }

    /**
     * @param mixed[]      $controller
     * @param class-string $annotationClass
     *
     * @throws ReflectionException
     */
    private function getAnnotationsByController(array $controller, string $annotationClass): array
    {
        /** @var AbstractController $controllerObject */
        list($controllerObject, $methodName) = $controller;

        $controllerReflectionObject = new ReflectionObject($controllerObject);
        $reflectionMethod = $controllerReflectionObject->getMethod($methodName);

        return $this->getAnnotations($reflectionMethod, $annotationClass);
    }

    /**
     * @param class-string $annotationClass
     *
     * @return mixed[]
     */
    private function getAnnotations(ReflectionMethod $method, string $annotationClass): array
    {
        $annotations = $this->reader->getMethodAnnotations($method);
        $annotations = array_filter($annotations, static function ($value) use ($annotationClass) {
            return $value instanceof $annotationClass;
        });

        $attributes = array_map(
            static function (ReflectionAttribute $attribute): object { return $attribute->newInstance(); },
            $method->getAttributes($annotationClass, ReflectionAttribute::IS_INSTANCEOF),
        );

        return array_merge($attributes, $annotations);
    }
}
