<?php
/**
 * Created by PhpStorm.
 * User: michael.wolf
 * Date: 25.06.2018
 * Time: 12:44
 */

namespace Ofeige\Rfc14Bundle\EventListener;


use Ofeige\Rfc14Bundle\Service\Pagination;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

class PaginationResponseListener
{
    /**
     * @var Pagination
     */
    private $pagination;

    /**
     * PaginationResponseListener constructor.
     * @param Pagination $pagination
     */
    public function __construct(Pagination $pagination)
    {
        $this->pagination = $pagination;
    }

    /**
     * @param FilterResponseEvent $event
     */
    public function onKernelResponse(FilterResponseEvent $event)
    {
        if ($this->pagination->getTotal() !== null) {
            $event->getResponse()->headers->set('x-rfc14-pagination-total', $this->pagination->getTotal());
        }
    }
}