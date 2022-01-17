<?php

declare(strict_types=1);

namespace Shopping\ApiTKUrlBundle;

use Shopping\ApiTKUrlBundle\DependencyInjection\ShoppingApiTKUrlExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class ShoppingApiTKUrlBundle extends Bundle
{
    public function getContainerExtension(): ?ExtensionInterface
    {
        return new ShoppingApiTKUrlExtension();
    }

    public function getPath(): string
    {
        return dirname(__DIR__);
    }
}
