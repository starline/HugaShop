<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.0
 *
 * ProductMoveAdmin
 */

namespace App\Controller\Admin\Product;

use HugaShop\Services\Design;
use App\Services\PaginationService;
use HugaShop\Models\Product\Product;
use App\Controller\BaseAdminController;
use HugaShop\Models\Warehouse\WarehousePurchase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProductMoveController extends BaseAdminController
{
    #[Route('/admin/product/{id}/move', requirements: ['id' => '\\d+'], name: 'ProductMoveAdmin')]
    public function index(int $id): Response
    {
        $this->checkAdminAccess('warehouse');

        if (!$product = Product::getProduct($id)) {
            return $this->redirectToRoute('ProductListAdmin');
        }

        $filter = PaginationService::initFilter();
        $filter['product_id'] = $product->id;

        $purchases_count = WarehousePurchase::countProductPurchases($filter);
        $purchases = WarehousePurchase::getProductPurchases($filter, ['warehouse_move', 'warehouse_move.place']);

        Design::assign('product', $product);
        Design::assign('purchases', $purchases);
        Design::assign('purchases_count', $purchases_count);
        Design::assign('pagination', PaginationService::getPagination($purchases_count, $filter));

        return $this->fetchResponse('product/product_move.tpl');
    }
}
