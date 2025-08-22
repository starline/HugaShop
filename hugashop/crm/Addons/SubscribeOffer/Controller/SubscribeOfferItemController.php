<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.1
 */

namespace HugaShop\Addons\SubscribeOffer\Controller;

use HugaShop\Services\Design;
use HugaShop\Services\Helper;
use App\Controller\BaseAdminController;
use HugaShop\Addons\BaseAddonTrait;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use HugaShop\Addons\SubscribeOffer\Models\SubscribeOffer;

final class SubscribeOfferItemController extends BaseAdminController
{
    use BaseAddonTrait;

    #[Route('/SubscribeOffer/{id}', requirements: ['id' => '\\d+'], name: 'AddonSubscribeOfferItem', priority: 20)]
    public function item(int $id): Response
    {
        $this->checkAdminAccess('addon');

        if (!$request = SubscribeOffer::getOne($id)) {
            return $this->redirectToRoute('AddonSubscribeOfferList');
        }

        if (!empty($request->user_agent)) {
            $request->user_agent = Helper::getUserAgentInfo($request->user_agent);
        }

        Design::assign('request', $request);
        Design::assign('addon', $this->getAddon());

        return $this->fetchAddonResponse('item.tpl');
    }
}
