<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.0
 *
 * ProductOrdersAdmin
 */

namespace App\Controller\Admin\Product;

use HugaShop\Services\Design;
use App\Services\PaginationService;
use HugaShop\Models\Order\Order;
use HugaShop\Models\Product\Product;
use App\Controller\BaseAdminController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProductOrdersController extends BaseAdminController
{
    #[Route('/admin/product/{id}/orders', requirements: ['id' => '\\d+'], name: 'ProductOrdersAdmin')]
    public function index(int $id): Response
    {
        $this->checkAdminAccess('order');

        if (!$product = Product::getProduct($id)) {
            return $this->redirectToRoute('ProductListAdmin');
        }

        Design::assign('product', $product);

        $this->assignOrders($product->id);

        return $this->fetchResponse('product/product_orders.tpl');
    }

    /**
     * Assign product orders to design
     */
    private function assignOrders(int $product_id): void
    {
        $filter = PaginationService::initFilter();
        $filter['product_id'] = $product_id;

        $orders_count = Order::getOrdersCount($filter);
        $orders = Order::getOrders($filter, join: [
            'delivery_method',
            'payment_method',
            'purchases',
            'purchases.product',
            'purchases.product.image',
            'labels'
        ]);

        $paid_filter = ['paid' => 1, 'product_id' => $product_id];
        $orders_paid_price = Order::getOrdersPrice($paid_filter);

        Design::assign('pagination', PaginationService::getPagination($orders_count, $filter));
        Design::assign('orders', $orders);
        Design::assign('orders_count', $orders_count);
        Design::assign('orders_paid_price', $orders_paid_price);
    }
}
