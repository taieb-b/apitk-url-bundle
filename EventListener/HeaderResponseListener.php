<?php

namespace Ofeige\Rfc14Bundle\EventListener;

use Ofeige\Rfc14Bundle\Service\Rfc14Service;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

class HeaderResponseListener
{
    /**
     * @var Rfc14Service
     */
    private $rfc14Service;

    /**
     * PaginationResponseListener constructor.
     * @param Rfc14Service $rfc14Service
     */
    public function __construct(Rfc14Service $rfc14Service)
    {
        $this->rfc14Service = $rfc14Service;
    }

    /**
     * @param FilterResponseEvent $event
     */
    public function onKernelResponse(FilterResponseEvent $event)
    {
        foreach ($this->rfc14Service->getHeaderInformation() as $key => $value) {
            $event->getResponse()->headers->set('x-rfc14-' . $key, $value);
        }
    }
}