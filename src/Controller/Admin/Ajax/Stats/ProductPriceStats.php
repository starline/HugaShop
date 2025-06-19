<?php

namespace App\Controller\Admin\Ajax\Stats;

use HugaShop\Api\Request;
use HugaShop\Api\Statistics;
use App\Controller\BaseAdminController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

class ProductPriceStats extends BaseAdminController
{
    #[Route('/admin/ajax/stats/product-price', name: 'ProductPriceStatsAdmin')]
    public function index()
    {
        if (!$this->checkAdminAccess('product_price') || !Request::checkCSRF()) {
            throw $this->createNotFoundException('Access denied CSRF');
        }

        $product_id = Request::post('product_id', 'int');
        if (empty($product_id)) {
            return new JsonResponse([]);
        }

        $type = Request::post('type');

        $result = Statistics::productPriceHistoryByDay($product_id, $type);
        return new JsonResponse($result);
    }
}
