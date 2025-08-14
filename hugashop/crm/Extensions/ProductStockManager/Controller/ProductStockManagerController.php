<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.1
 */

namespace HugaShop\Extensions\ProductStockManager\Controller;

use HugaShop\Services\Design;
use HugaShop\Services\Request;
use App\Services\PaginationService;
use App\Controller\BaseAdminController;
use HugaShop\Extensions\BaseExtensionTrait;
use Symfony\Component\Routing\Attribute\Route;
use HugaShop\Extensions\ProductStockManager\Models\Product;

final class ProductStockManagerController extends BaseAdminController
{
    use BaseExtensionTrait;

    #[Route('/ProductStockManager', name: 'ExtProductStockManager', priority: 20)]
    public function index()
    {
        $filter = PaginationService::initFilter();

        if ($keyword = Request::get('keyword')) {
            $filter['keyword'] = $keyword;
            Design::assign('keyword', $keyword);
        }

        // Текущий фильтр
        if ($query_filter = Request::get('filter', 'string')) {
            if ($query_filter == 'stagnation') {
                $filter['stagnation'] = 1;
            } elseif ($query_filter == 'purchase') {
                $filter['purchase'] = 1;
            } elseif ($query_filter == 'top') {
                $filter['top'] = 1;
            }

            $filter['date_from'] = Request::get('date_from');

            Design::assign('date_from', $filter['date_from']);
            Design::assign('filter', $query_filter);
        }

        $products       = Product::getProducts($filter, join: ['image', 'movements']);
        $products_count = Product::countProducts($filter);

        if ($products->isNotEmpty()) {
            foreach ($products as $product) {
                $product->profit_price = $product->price - $product->cost_price;
            }
        }

        Design::assign('products',          $products);
        Design::assign('products_count',    $products_count);
        Design::assign('pagination',        PaginationService::getPagination($products_count, $filter));
        Design::assign('extension',         $this->getExtension());

        return $this->fetchExtResponse('product_list.tpl');
    }
}
