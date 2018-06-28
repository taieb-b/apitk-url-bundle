<?php

namespace Ofeige\Rfc14Bundle\EventListener;

use Ofeige\Rfc14Bundle\Service\Rfc14Service;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

class PaginationResponseListener
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
        if ($this->rfc14Service->getPaginationTotal() !== null) {
            $event->getResponse()->headers->set('x-rfc14-pagination-total', $this->rfc14Service->getPaginationTotal());
        }
    }
}