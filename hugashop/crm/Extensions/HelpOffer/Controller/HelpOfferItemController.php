<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.1
 */

namespace HugaShop\Extensions\HelpOffer\Controller;

use HugaShop\Services\Design;
use HugaShop\Services\Helper;
use App\Controller\BaseAdminController;
use HugaShop\Extensions\BaseExtensionTrait;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use HugaShop\Extensions\HelpOffer\Models\HelpOffer;

final class HelpOfferItemController extends BaseAdminController
{
    use BaseExtensionTrait;

    #[Route('/HelpOffer/{id}', requirements: ['id' => '\\d+'], name: 'ExtHelpOfferItem', priority: 20)]
    public function item(int $id): Response
    {
        $this->checkAdminAccess('extension');

        if (!$request = HelpOffer::getOne($id)) {
            return $this->redirectToRoute('ExtHelpOfferList');
        }

        if (!empty($request->user_agent)) {
            $request->user_agent = Helper::getUserAgentInfo($request->user_agent);
        }

        Design::assign('request', $request);
        Design::assign('extension', $this->getExtension());

        return $this->fetchExtResponse('item.tpl');
    }
}
