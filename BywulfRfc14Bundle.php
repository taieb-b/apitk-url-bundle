<?php

namespace Bywulf\Rfc14Bundle;

use Bywulf\Rfc14Bundle\DependencyInjection\ByWulfRfc14Extension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class BywulfRfc14Bundle extends Bundle
{
    public function getContainerExtension()
    {
        return new ByWulfRfc14Extension();
    }
}