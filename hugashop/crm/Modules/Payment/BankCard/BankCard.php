<?php

namespace HugaShop\Modules\Payment\BankCard;

use HugaShop\Api\Order\Order;
use HugaShop\Api\Config;
use HugaShop\Api\Design;
use HugaShop\Api\Order\OrderPayment;
use HugaShop\Api\Finance\FinanceCurrency;

class BankCard
{
    public function checkoutForm($order_id, $view_type)
    {

        $order = Order::getOrder((int)$order_id);
        $payment_method = OrderPayment::getOne($order->payment_method_id);
        $amount = FinanceCurrency::priceConvert($order->payment_price, $payment_method->currency_id, false);

        Design::assign('payment_method', $payment_method);

        // Проверим сущестование файла
        if (!empty($view_type)) {
            $file_path = Config::get('payment_dir') . $payment_method->module . "/" . $payment_method->module . "_" . "$view_type.tpl";
            if (is_file($file_path)) {
                return Design::fetch($file_path);
            }
        }

        return false;
    }
}
