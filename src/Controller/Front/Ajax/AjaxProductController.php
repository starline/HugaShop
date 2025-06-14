<?php

namespace App\Controller\Front\Ajax;

use HugaShop\Api\Image;
use HugaShop\Api\Product\Product;
use HugaShop\Api\Request;
use App\Event\SearchEvent;
use HugaShop\Api\Settings;
use App\Controller\BaseFrontController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

class AjaxProductController extends BaseFrontController
{

    #[Route('/ajax/product/search', name: 'AjaxProductSearch', priority: 1)]
    public function search(): JsonResponse
    {

        // Поиск (без 'string' - потому-что сжирает запятые)
        $query = Request::get('query', 'string');
        if (!empty($query)) {
            $filter['keyword'] = $query;

            $this->setEvent(new SearchEvent($query));
        }

        $filter['limit'] = Settings::getParam('products_num');
        $filter['visible'] = 1;

        $products = Product::getProducts($filter, ['image']);
        $suggestions = [];

        foreach ($products as $product) {

            $product_sug = new \stdClass();
            $product_sug->name = $product->name;
            $product_sug->id = $product->id;
            if (!empty($product->image_filename)) {
                $product_sug->image = Image::getURL($product->image_filename, 60, 60);
            }

            $suggestion = new \stdClass();
            $suggestion->value = $product->name;
            $suggestion->data = $product_sug;

            $suggestions[] = $suggestion;
        }

        if (count($suggestions) == 0) {
            $suggestion = new \stdClass();
            $suggestion->value = 'По вышему запрос не найдено товаров';
            $suggestions[] = $suggestion;
        }

        $res = new \stdClass();
        $res->query = $query;
        $res->suggestions = $suggestions;

        return new JsonResponse($res);
    }
}
