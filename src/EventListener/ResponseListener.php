<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.0
 *
 */

namespace App\EventListener;

use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

class ResponseListener
{
    #[AsEventListener]
    public function onKernelResponse(ResponseEvent $event): void
    {

        //$response = $event->getResponse();
        //$content = $response->getContent();
        // ...
    }
}
