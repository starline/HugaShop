<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.1
 *
 * ProductMoveAdmin
 */

namespace App\Controller\Admin\Product;

use HugaShop\Services\Design;
use App\Services\PaginationService;
use HugaShop\Models\Product\Product;
use App\Controller\BaseAdminController;
use HugaShop\Models\Warehouse\WarehouseMove;
use HugaShop\Services\Request;
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
        $filter['status'] = Request::getInt('status');

        $keyword = Request::get('keyword', 'string');
        if (!empty($keyword)) {
            $filter['keyword'] = $keyword;
            Design::assign('keyword', $keyword);
        }

        $movements = WarehouseMove::getMovements($filter, ['images', 'purchases', 'purchases.product', 'purchases.product.image']);
        $movements_count = WarehouseMove::countMovements($filter);

        if (in_array($filter['status'], [0, 1], true)) {
            $total = WarehouseMove::getMovementsTotals($filter);
            Design::assign('total', $total);
        }

        Design::assign('product', $product);
        Design::assign('movements', $movements);
        Design::assign('movements_count', $movements_count);
        Design::assign('status', $filter['status']);
        Design::assign('pagination', PaginationService::getPagination($movements_count, $filter));

        return $this->fetchResponse('product/product_move.tpl');
    }
}
