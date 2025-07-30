<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.1
 */

namespace HugaShop\Extensions\ProductPriceRequest\Controller;

use HugaShop\Services\Design;
use HugaShop\Services\Request;
use HugaShop\Services\NotifierFactory;
use App\Controller\BaseFrontController;
use HugaShop\Extensions\BaseExtensionTrait;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use HugaShop\Extensions\ProductPriceRequest\Models\PriceRequest;
use HugaShop\Extensions\ProductPriceRequest\Services\NotifyService;
use HugaShop\Models\Product\Product;

final class PriceRequestController extends BaseFrontController
{
    use BaseExtensionTrait;

    #[Route('/ProductPriceRequest/form', name: 'ExtPriceRequestForm', priority: 21)]
    public function form(): Response
    {

        if (!Request::checkCSRF() and !Request::post('product_id')) {
            throw $this->createNotFoundException('Extension is not enabled...');
        }

        $data = new \stdClass();
        $data->product_id = Request::post('product_id');
        $data->name       = '';
        $data->phone      = '';
        $data->email      = '';
        $data->link       = '';

        $product = Product::getProduct($data->product_id, join: ['image']);

        Design::assign('product', $product);
        Design::assign('form_data', $data);

        return $this->fetchExtResponse('form.tpl', 'price_request');
    }


    #[Route('/ProductPriceRequest', name: 'ExtPriceRequest', methods: ['POST'], priority: 20)]
    public function request(): Response
    {
        if (Request::checkCSRF()) {
            $data = new \stdClass();
            $data->product_id = Request::postInt('product_id');
            $data->name       = Request::post('name');
            $data->phone      = Request::post('phone');
            $data->email      = Request::post('email');
            $data->link       = Request::post('link');

            Design::assign('form_data', $data);

            $invalid = [];
            foreach (['name', 'phone', 'email', 'link'] as $field) {
                if (empty($data->$field)) {
                    $invalid[] = $field;
                }
            }

            if (empty($invalid)) {
                $data->ip = $_SERVER['REMOTE_ADDR'];
                $request  = PriceRequest::createOne($data);
                $product  = Product::getProduct($request->product_id);

                NotifierFactory::sendToManagers([
                    NotifyService::class,
                    'requestToAdmin'
                ], [
                    'request' => $request,
                    'product' => $product
                ]);

                return $this->fetchExtResponse('form.tpl', 'request_sent');
            }

            Design::assign('form_invalid', $invalid);
        }

        return $this->fetchExtResponse('form.tpl', 'price_request');
    }
}
