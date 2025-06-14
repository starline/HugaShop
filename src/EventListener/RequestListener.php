<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.2
 *
 */

namespace App\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\RequestEvent;

class RequestListener
{
    #[AsEventListener(priority: 2304)]
    public function onKernelRequest(RequestEvent $event): void
    {
        // HugaShop init
        //if ($event->isMainRequest()) {
        // ...
        //}
    }
}
