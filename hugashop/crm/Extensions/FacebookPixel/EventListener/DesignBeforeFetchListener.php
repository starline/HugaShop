<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.6
 *
 */

namespace HugaShop\Extensions\FacebookPixel\EventListener;

use HugaShop\Services\Request;
use App\Event\DesignBeforeFetchEvent;
use HugaShop\Extensions\BaseExtension;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

class DesignBeforeFetchListener extends BaseExtension
{

    /**
     * Reques
     * @link https://developers.facebook.com/docs/marketing-api/conversions-api/parameters/fbp-and-fbc
     * @param DesignBeforeFetchEvent $event
     */
    #[AsEventListener]
    public function onDesignBeforeFetchEvent(DesignBeforeFetchEvent $event): void
    {
        if (empty($this->settings->enabled)) {
            return;
        }

        if (0) {
            $fbclid_get = Request::get('fbclid');

            // Example: fb.2.1736634312388.fbclid
            if ($fbc_cookie = Request::getCookie('_fbc', false)) {
                $fbc_cookie_arr = explode('.', $fbc_cookie);
                $fbclid_cookie = empty($fbc_cookie_arr[3]) ? null : $fbc_cookie_arr[3];
            }

            // Set FB Cookie
            if (!empty($fbclid_get) and (empty($fbclid_cookie) || $fbclid_cookie == 'fbclid' || $fbclid_get != $fbclid_cookie)) {
                $cookie_val = 'fb.2.' . time() . '.' . $fbclid_get;
                Request::setCookie('_fbc', $cookie_val, 90, '/', false);
            }
        }
    }
}
