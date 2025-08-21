<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.0
 */

namespace HugaShop\Extensions\SubscribeOffer\Controller;

use HugaShop\Services\Design;
use HugaShop\Services\Helper;
use App\Controller\BaseAdminController;
use HugaShop\Extensions\BaseExtensionTrait;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use HugaShop\Extensions\SubscribeOffer\Models\SubscribeOffer;

final class SubscribeOfferItemController extends BaseAdminController
{
    use BaseExtensionTrait;

    #[Route('/SubscribeOffer/{id}', requirements: ['id' => '\\d+'], name: 'ExtSubscribeOfferItem', priority: 20)]
    public function item(int $id): Response
    {
        $this->checkAdminAccess('extension');

        if (!$request = SubscribeOffer::getOne($id)) {
            return $this->redirectToRoute('ExtSubscribeOfferList');
        }

        if (!empty($request->user_agent)) {
            $request->user_agent = Helper::getUserAgentInfo($request->user_agent);
        }

        Design::assign('request', $request);
        Design::assign('extension', $this->getExtension());

        return $this->fetchExtResponse('item.tpl');
    }
}
