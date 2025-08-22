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
use HugaShop\Services\Secure;
use HugaShop\Services\Request;
use HugaShop\Models\Product\Product;
use HugaShop\Services\NotifierFactory;
use App\Controller\BaseFrontController;
use HugaShop\Addons\BaseAddonTrait;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use HugaShop\Addons\ProductPriceRequest\Models\PriceRequest;
use HugaShop\Addons\ProductPriceRequest\Services\NotifyService;

final class PriceRequestController extends BaseFrontController
{
    use BaseAddonTrait;

    #[Route('/ProductPriceRequest/form', name: 'ExtPriceRequestForm', priority: 21)]
    public function form(): Response
    {

        if (!($product_id = Request::input('product_id'))) {
            throw $this->createNotFoundException('Product not found...');
        }

        $product = Product::getProduct($product_id, join: ['image']);

        if (!empty($request = Secure::getInputAcces(PriceRequest::getFields()))) {

            if (Helper::checkCaptcha()) {
                $request->ip            = $_SERVER['REMOTE_ADDR'];
                $request->user_agent    = $_SERVER['HTTP_USER_AGENT']; # Browser
                $request                = PriceRequest::createOne($request);

                NotifierFactory::sendToManagers([
                    NotifyService::class,
                    'priceRequestToAdmin'
                ], [
                    'request' => $request,
                    'product' => $product
                ]);

                return $this->fetchAddonResponse('form.tpl', 'request_sent');
            } else {
                Design::assign('error', 'captcha');
            }
        }

        Design::assign('product', $product);
        Design::assign('request', $request);

        return $this->fetchAddonResponse('form.tpl', 'price_request');
    }
}
