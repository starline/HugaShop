<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.4
 *
 */

namespace HugaShop\Addons\CmlExchange\Controller;

use HugaShop\Services\Request;
use App\Controller\BaseFrontController;
use HugaShop\Addons\BaseAddonTrait;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use HugaShop\Addons\CmlExchange\Services\CmlExchangeService;

final class CmlExchangeController extends BaseFrontController
{

    use BaseAddonTrait;

    /**
     * Список страниц
     */
    #[Route('/CmlExchange/exchange', name: 'ExtCmlExchange', priority: 1)]
    public function exchange(): Response
    {

        if (Request::get('mode') === 'checkauth' && !self::checkAuth()) {
            return new Response("failure\n");
        }

        return new Response(CmlExchangeService::handle());
    }


    /**
     * Check authentication parameters
     */
    public static function checkAuth(): bool
    {
        $login      = self::getSettings()->login ?? '';
        $password   = self::getSettings()->password ?? '';

        $reqLogin = Request::get('login');
        $reqPass  = Request::get('password') ?: Request::get('pass');

        if ($login === '' && $password === '') {
            return true;
        }

        return $login === $reqLogin && $password === $reqPass;
    }
}
