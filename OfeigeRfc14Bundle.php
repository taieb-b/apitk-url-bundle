<?php

namespace Ofeige\Rfc14Bundle;

use Ofeige\Rfc14Bundle\DependencyInjection\OfeigeRfc14Extension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class OfeigeRfc14Bundle extends Bundle
{
    public function getContainerExtension()
    {
        return new OfeigeRfc14Extension();
    }
}