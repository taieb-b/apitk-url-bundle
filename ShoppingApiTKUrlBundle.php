<?php

namespace Shopping\ApiTKUrlBundle;

use Shopping\ApiTKUrlBundle\DependencyInjection\ShoppingApiTKUrlExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class ShoppingApiTKUrlBundle extends Bundle
{
    public function getContainerExtension()
    {
        return new ShoppingApiTKUrlExtension();
    }
}
