<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.1
 */

namespace HugaShop\Extensions\ProductPriceRequest\Controller;

use HugaShop\Services\Design;
use HugaShop\Services\Secure;
use HugaShop\Services\Request;
use HugaShop\Models\Product\Product;
use HugaShop\Services\NotifierFactory;
use App\Controller\BaseFrontController;
use HugaShop\Extensions\BaseExtensionTrait;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use HugaShop\Extensions\ProductPriceRequest\Models\PriceRequest;
use HugaShop\Extensions\ProductPriceRequest\Services\NotifyService;

final class PriceRequestController extends BaseFrontController
{
    use BaseExtensionTrait;

    #[Route('/ProductPriceRequest/form', name: 'ExtPriceRequestForm', priority: 21)]
    public function form(): Response
    {

        if (!($product_id = Request::input('product_id'))) {
            throw $this->createNotFoundException('Product not found...');
        }

        $product = Product::getProduct($product_id, join: ['image']);

        if (!empty($request = Secure::getInputAcces(PriceRequest::getFields()))) {

            $request->ip = $_SERVER['REMOTE_ADDR'];
            $request = PriceRequest::createOne($request);

            NotifierFactory::sendToManagers([
                NotifyService::class,
                'priceRequestToAdmin'
            ], [
                'request' => $request,
                'product' => $product
            ]);

            return $this->fetchExtResponse('form.tpl', 'request_sent');
        }

        Design::assign('product', $product);
        Design::assign('request', $request);

        return $this->fetchExtResponse('form.tpl', 'price_request');
    }
}
