<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.2
 */

namespace HugaShop\Addons\HelpOffer\Controller;

use HugaShop\Services\Design;
use HugaShop\Services\Helper;
use App\Controller\BaseAdminController;
use HugaShop\Addons\BaseAddonTrait;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use HugaShop\Addons\HelpOffer\Models\HelpOffer;

final class HelpOfferItemController extends BaseAdminController
{
    use BaseAddonTrait;

    #[Route('/HelpOffer/{id}', requirements: ['id' => '\\d+'], name: 'ExtHelpOfferItem', priority: 20)]
    public function item(int $id): Response
    {
        $this->checkAdminAccess('addon');

        if (!$request = HelpOffer::getOne($id)) {
            return $this->redirectToRoute('ExtHelpOfferList');
        }

        if (!empty($request->user_agent)) {
            $request->user_agent = Helper::getUserAgentInfo($request->user_agent);
        }

        Design::assign('request', $request);
        Design::assign('addon', $this->getAddon());

        return $this->fetchAddonResponse('item.tpl');
    }
}
