<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.3
 */

namespace HugaShop\Addons\ProductPriceRequest\Controller;

use HugaShop\Services\Design;
use HugaShop\Services\Helper;
use App\Controller\BaseAdminController;
use HugaShop\Addons\BaseAddonTrait;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use HugaShop\Addons\ProductPriceRequest\Models\PriceRequest;

final class PriceRequestItemController extends BaseAdminController
{
    use BaseAddonTrait;

    #[Route('/ProductPriceRequest/{id}', requirements: ['id' => '\d+'], name: 'ExtPriceRequestItem', priority: 20)]
    public function item(int $id): Response
    {
        $this->checkAdminAccess('addon');

        if (!$request = PriceRequest::getOne($id, ['product', 'product.image'])) {
            return $this->redirectToRoute('ExtPriceRequestList');
        }

        if (!empty($request->user_agent)) {
            $request->user_agent = Helper::getUserAgentInfo($request->user_agent);
        }

        Design::assign('request', $request);
        Design::assign('addon', $this->getAddon());

        return $this->fetchAddonResponse('item.tpl');
    }
}
