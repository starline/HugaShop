<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.1
 *
 */

namespace App\Controller\Admin\Finance;

use HugaShop\Services\Design;
use HugaShop\Models\Product\Product;
use HugaShop\Models\Order\OrderPayment;
use App\Controller\BaseAdminController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class StatsController extends BaseAdminController
{
    #[Route('/admin/finance/stats', name: 'StatsAdmin')]
    public function index(): Response
    {

        $this->checkAdminAccess('stats');

        $payment_methods = OrderPayment::getPaymentMethods();

        $total =  new \stdClass();
        $total->products_count = Product::countProducts();
        $total->sum_wholesale_price = 0;
        $total->sum_price = 0;
        $total->sum_stock = 0;

        $products = Product::getList();
        foreach ($products as $v) {

            // Пропускаем товары "Под заказ", ∞, отсутствующие
            if (!empty($v->stock) and empty($v->custom)) {
                $total->sum_stock += $v->stock;
                $total->sum_price += ($v->price * $v->stock);
                $total->sum_wholesale_price += ($v->cost_price * $v->stock);
            }
        }

        Design::assign('payment_methods', $payment_methods);
        Design::assign('total', $total);

        return $this->fetchResponse('finance/stats.tpl');
    }
}
