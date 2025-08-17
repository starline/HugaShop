<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 3.1
 *
 */

namespace App\Controller\Front;

use HugaShop\Services\Config;
use HugaShop\Services\Design;
use HugaShop\Services\Request;
use HugaShop\Models\Order\Order;
use HugaShop\Models\Finance\FinanceCurrency;
use HugaShop\Models\Order\OrderPayment;
use HugaShop\Models\Order\OrderPurchase;
use HugaShop\Models\Order\OrderDelivery;
use App\Controller\BaseFrontController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class OrderController extends BaseFrontController
{

    #[Route('/order', name: 'preOrder', priority: 10)]
    #[Route('/order/{id}/{order_token}', requirements: ['id' => '\d+'], name: 'Order', priority: 10)]
    public function order(?int $id = null, ?string $order_token = null): Response
    {

        if (empty($id)) {
            if (!empty(Request::getSession('order_id'))) {
                if (!empty($order = Order::getOrder(intval(Request::getSession('order_id'))))) {
                    return $this->redirectToRoute('Order', ['id' => $order->id, 'order_token' => $order->token]);
                }
            }
        } else {
            $order = Order::getOrder($id);
        }

        if (empty($order) || $order->token != $order_token) {
            return $this->redirectToRoute('Cart');
        }

        // подключаем плагин Smarty
        Design::setFunctionPlugin('get_payment_module_html',    OrderPayment::class,    'getPaymentModuleHtml');    # Форма оплаты
        Design::setFunctionPlugin('get_delivery_module_html',   OrderDelivery::class,   'getDeliveryModuleHtml');   # Выводим модуль доставки

        $order->subtotal_price = 0;
        $order->purchases_count = 0;

        $purchases = OrderPurchase::getPurchases(['order_id' => $order->id], ['image', 'product', 'category']);
        foreach ($purchases as $purch) {
            $order->subtotal_price += $purch->product->price * $purch->amount;
            $order->purchases_count += $purch->amount;
        }

        // Способ доставки
        $delivery = OrderDelivery::getOne($order->delivery_id);

        // Способ оплаты
        if ($order->payment_method_id) {
            $payment_method = OrderPayment::getOne($order->payment_method_id);
            Design::assign('payment_method', $payment_method);

            // Валюта оплаты
            $payment_currency = FinanceCurrency::getCurrency(intval($payment_method->currency_id));
            Design::assign('payment_currency', $payment_currency);
        }

        // Все Способы доставки
        $deliveries = OrderDelivery::getDeliveryMethods(['enabled' => 1]);

        // Все Варианты оплаты
        $payment_methods = OrderPayment::getPaymentMethods(['enabled' => 1, 'enabled_public' => 1]);

        Design::assign('noindex', true); # Закрываем от индексации
        Design::assign('order', $order);
        Design::assign('payment_methods', $payment_methods);
        Design::assign('delivery', $delivery);
        Design::assign('deliveries', $deliveries);
        Design::assign('purchases', $purchases);

        // Выводим заказ
        if (Request::get('type') === 'print') {
            return $this->fetchResponse("order_print.tpl", 'content', Config::get('templates_dir') . "/admin/html/order/"); # fetch without wrapper
        }

        return $this->fetchResponse('order.tpl');
    }
}
