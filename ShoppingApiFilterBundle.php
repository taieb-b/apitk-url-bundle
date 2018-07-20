<?php

namespace Shopping\ApiFilterBundle;

use Shopping\ApiFilterBundle\DependencyInjection\ShoppingApiFilterExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class ShoppingApiFilterBundle extends Bundle
{
    public function getContainerExtension()
    {
        return new ShoppingApiFilterExtension();
    }
}