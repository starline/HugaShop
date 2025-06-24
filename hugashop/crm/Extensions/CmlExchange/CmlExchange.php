<?php

/**
 * HugaShop - Sell anything
 * 
 * @author Andi Huga
 * @version 1.2
 *
 */

namespace HugaShop\Extensions\CmlExchange;

use HugaShop\Models\Request;
use HugaShop\Extensions\CmlExchange\Services\CmlExchangeService;
use HugaShop\Extensions\BaseExtension;
use Symfony\Component\HttpFoundation\Response;

class CmlExchange extends BaseExtension
{

    /**
     * Основной webhook обмена с 1С
     */
    public function webhook(array $params = []): Response
    {
        if (Request::get('mode') === 'checkauth' && !$this->checkAuth()) {
            return new Response("failure\n");
        }
        $service = new CmlExchangeService();
        return $service->handle($params);
    }


    /**
     * Check authentication parameters
     */
    public function checkAuth(): bool
    {
        $login = $this->ext_settings->login ?? '';
        $password = $this->ext_settings->password ?? '';

        $reqLogin = Request::get('login');
        $reqPass  = Request::get('password') ?: Request::get('pass');

        if ($login === '' && $password === '') {
            return true;
        }

        return $login === $reqLogin && $password === $reqPass;
    }
}
