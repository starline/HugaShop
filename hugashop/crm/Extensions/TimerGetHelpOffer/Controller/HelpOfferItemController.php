<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.0
 */

namespace HugaShop\Extensions\TimerGetHelpOffer\Controller;

use HugaShop\Services\Design;
use HugaShop\Services\Helper;
use App\Controller\BaseAdminController;
use HugaShop\Extensions\BaseExtensionTrait;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use HugaShop\Extensions\TimerGetHelpOffer\Models\HelpOffer;

final class HelpOfferItemController extends BaseAdminController
{
    use BaseExtensionTrait;

    #[Route('/TimerGetHelpOffer/{id}', requirements: ['id' => '\\d+'], name: 'ExtTimerGetHelpOfferItem', priority: 20)]
    public function item(int $id): Response
    {
        $this->checkAdminAccess('extension');

        if (!$request = HelpOffer::getOne($id)) {
            return $this->redirectToRoute('ExtTimerGetHelpOfferList');
        }

        if (!empty($request->user_agent)) {
            $request->user_agent = Helper::getUserAgentInfo($request->user_agent);
        }

        Design::assign('request', $request);
        Design::assign('extension', $this->getExtension());

        return $this->fetchExtResponse('item.tpl');
    }
}
