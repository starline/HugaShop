<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.0
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

        $products       = Product::getProducts($filter, join: ['image']);
        $products_count = Product::countProducts($filter);

        Design::assign('products', $products);
        Design::assign('products_count', $products_count);
        Design::assign('pagination', PaginationService::getPagination($products_count, $filter));
        Design::assign('extension', $this->getExtension());

        return $this->fetchExtResponse('product_list.tpl');
    }
}
