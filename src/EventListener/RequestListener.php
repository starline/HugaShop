<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.3
 *
 */

namespace App\EventListener;

use App\Services\LocaleService;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

class RequestListener
{
    #[AsEventListener(priority: 2304)]
    public function onKernelRequest(RequestEvent $event): void
    {
        if ($event->isMainRequest()) {
            LocaleService::prepare($event->getRequest());
        }
    }
}
