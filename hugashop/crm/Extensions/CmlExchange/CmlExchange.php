<?php

/**
 * HugaShop - Sell anything
 * 
 * @author Andi Huga
 * @version 1.4
 *
 */

namespace HugaShop\Extensions\CmlExchange;

use HugaShop\Services\Request;
use HugaShop\Extensions\CmlExchange\Services\CmlExchangeService;
use HugaShop\Extensions\BaseExtension;
use Symfony\Component\HttpFoundation\Response;

class CmlExchange extends BaseExtension
{

    /**
     * Основной webhook обмена с 1С
     */
    public static function webhook(array $params = []): Response
    {
        if (Request::get('mode') === 'checkauth' && !self::checkAuth()) {
            return new Response("failure\n");
        }
        $service = new CmlExchangeService();
        return $service->handle($params);
    }


    /**
     * Check authentication parameters
     */
    public static function checkAuth(): bool
    {
        $login = self::getSettings()->login ?? '';
        $password = self::getSettings()->password ?? '';

        $reqLogin = Request::get('login');
        $reqPass  = Request::get('password') ?: Request::get('pass');

        if ($login === '' && $password === '') {
            return true;
        }

        return $login === $reqLogin && $password === $reqPass;
    }
}
