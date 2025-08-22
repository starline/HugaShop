<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.2
 *
 * Webhook controller for Binotel calls.
 */

namespace HugaShop\Addons\Leads\Controller;

use App\Controller\BaseFrontController;
use HugaShop\Addons\BaseAddonTrait;
use HugaShop\Addons\Leads\Services\BinotelLeadService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class BinotelController extends BaseFrontController
{
    use BaseAddonTrait;

    #[Route('/Leads/binotel/webhook', name: 'ExtLeadsBinotelWebhook', priority: 20)]
    public function webhook(Request $request): Response
    {
        $service = new BinotelLeadService();
        $service->handleIncomingCall($request->request->all());

        return new Response('ok');
    }
}
