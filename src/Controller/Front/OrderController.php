<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 3.0
 *
 */

namespace App\Controller\Front;

use HugaShop\Api\Config;
use HugaShop\Api\Design;
use HugaShop\Api\Request;
use HugaShop\Api\Order\Order;
use HugaShop\Api\Finance\FinanceCurrency;
use HugaShop\Api\Order\OrderPayment;
use HugaShop\Api\Order\OrderPurchase;
use HugaShop\Api\Order\OrderDelivery;
use App\Controller\BaseFrontController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class OrderController extends BaseFrontController
{

    #[Route('/order', name: 'preOrder', priority: 5)]
    #[Route('/order/{id}/{order_url}', requirements: ['id' => '\d+'], name: 'Order', priority: 6)]
    public function order(?int $id = null, ?string $order_url = null): Response
    {

        if (empty($id)) {
            if (!empty(Request::getSession('order_id'))) {
                if (!empty($order = Order::getOrder(intval(Request::getSession('order_id'))))) {
                    return $this->redirectToRoute('Order', ['id' => $order->id, 'order_url' => $order->url]);
                }
            }
        } else {
            $order = Order::getOrder($id);
        }

        if (empty($order) || $order->url != $order_url) {
            return $this->redirectToRoute('Cart');
        }

        // подключаем плагин Smarty
        Design::setFunctionPlugin("get_payment_module_html", OrderPayment::class, 'getPaymentModuleHtml');    # Форма оплаты
        Design::setFunctionPlugin("get_delivery_module_html", OrderDelivery::class, 'getDeliveryModuleHtml'); # Выводим модуль доставки

        $order->subtotal_price = 0;
        $order->purchases_count = 0;
        $order->variants_sku = [];

        $purchases = OrderPurchase::getPurchases(['order_id' => $order->id], ['image', 'product', 'category']);
        foreach ($purchases as $purch) {
            $order->subtotal_price += $purch->variant->price * $purch->amount;
            $order->purchases_count += $purch->amount;
            $order->variants_sku[] = $purch->variant->sku;
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

            // Выбираем настройки способа оплаты
            $payment_settings = OrderPayment::getPaymentMethodSettings($payment_method->id);
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
            return $this->fetchResponse("order_print.tpl", 'content', Config::get('templates_dir') . "/admin/order/"); # fetch without wrapper
        }

        return $this->fetchResponse('order.tpl');
    }
}
