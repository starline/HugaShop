<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.1
 */

namespace HugaShop\Extensions\ProductPriceRequest\Controller;

use HugaShop\Services\Design;
use App\Controller\BaseAdminController;
use HugaShop\Extensions\BaseExtensionTrait;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
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

        Design::assign('request', $request);
        Design::assign('extension', $this->getExtension());

        return $this->fetchExtResponse('item.tpl');
    }
}
