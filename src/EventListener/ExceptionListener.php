<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.1
 *
 * Custom 404 error handler
 */

namespace App\EventListener;

use HugaShop\Services\Design;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

class ExceptionListener
{

    #[AsEventListener(event: KernelEvents::EXCEPTION)]
    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        if (!$exception instanceof NotFoundHttpException) {
            return;
        }

        Design::assign([
            'meta_title'       => 'Страница не найдена',
            'meta_description' => 'Страница не найдена',
        ]);

        $content = Design::fetch('404.tpl');
        $event->setResponse(new Response($content, Response::HTTP_NOT_FOUND));
    }
}
