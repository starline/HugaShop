<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.2
 */

namespace HugaShop\Extensions\ProductPriceRequest\Controller;

use HugaShop\Services\Design;
use HugaShop\Services\Helper;
use App\Controller\BaseAdminController;
use HugaShop\Extensions\BaseExtensionTrait;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use HugaShop\Extensions\ProductPriceRequest\Models\PriceRequest;

final class PriceRequestItemController extends BaseAdminController
{
    use BaseExtensionTrait;

    #[Route('/ProductPriceRequest/{id}', requirements: ['id' => '\d+'], name: 'ExtPriceRequestItem', priority: 20)]
    public function item(int $id): Response
    {
        $this->checkAdminAccess('extension');

        if (!$request = PriceRequest::getOne($id, ['product', 'product.image'])) {
            return $this->redirectToRoute('ExtPriceRequestList');
        }

        if (!empty($request->user_agent)) {
            $request->user_agent = Helper::getUserAgentInfo($request->user_agent);
        }

        Design::assign('request', $request);
        Design::assign('extension', $this->getExtension());

        return $this->fetchExtResponse('item.tpl');
    }
}
