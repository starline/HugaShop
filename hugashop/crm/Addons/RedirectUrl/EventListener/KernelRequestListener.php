<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.9
 *
 */

namespace HugaShop\Addons\RedirectUrl\EventListener;

use HugaShop\Services\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use HugaShop\Addons\RedirectUrl\Models\RedirectUrl;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

class KernelRequestListener
{

    /**
     * Kernel Event
     */
    #[AsEventListener(priority: 128)]
    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest() || Request::isAjax()) {
            return;
        }

        $uri = urldecode($event->getRequest()->getPathInfo()); # Example: /test-link

        // Исключаем /admin и всё, что под ним
        if (str_starts_with($uri, '/admin')) {
            return;
        }

        $links = RedirectUrl::getList(['enabled' => 1], cache: null);
        
        foreach ($links as $link) {
            [$pattern, $names] = $this->preparePattern($link->url);
            if (preg_match($pattern, $uri, $m)) {
                $redirect = $link->redirect;
                foreach ($m as $i => $v) {
                    if ($i === 0) continue;
                    $redirect = str_replace("[$i]", $v, $redirect);
                    if (isset($names[$i - 1])) {
                        $redirect = str_replace('[' . $names[$i - 1] . ']', $v, $redirect);
                    }
                }

                RedirectUrl::where('id', $link->id)->increment('transitions');
                Request::makeRedirect($redirect, '301');
            }
        }
    }


    /**
     * Pattern 
     */
    private function preparePattern(string $mask): array
    {
        $names = [];
        $regex = '';
        $offset = 0;

        while (preg_match('/\{(\w+)(?::([^}]+))?\}/', $mask, $m, PREG_OFFSET_CAPTURE, $offset)) {
            $regex .= preg_quote(substr($mask, $offset, $m[0][1] - $offset), '#');
            $names[] = $m[1][0];
            $regex .= '(' . ($m[2][0] ?? '[^/]+') . ')';
            $offset = $m[0][1] + strlen($m[0][0]);
        }

        $regex .= preg_quote(substr($mask, $offset), '#');

        return ['#^' . $regex . '$#u', $names];
    }
}
