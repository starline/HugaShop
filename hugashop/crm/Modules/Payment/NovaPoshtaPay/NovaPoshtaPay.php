<?php

namespace HugaShop\Modules\Payment\NovaPoshtaPay;

use HugaShop\Api\Order\Order;
use HugaShop\Api\Config;
use HugaShop\Api\Design;
use HugaShop\Api\Order\OrderPayment;
use HugaShop\Api\Finance\FinanceCurrency;

class NovaPoshtaPay
{
    public function checkoutForm($order_id, $view_type)
    {

        if (!empty($order_id)) {
            $order = Order::getOrder((int)$order_id);

            $payment_method = OrderPayment::getOne($order->payment_method_id);
            $payment_currency = FinanceCurrency::getCurrency(intval($payment_method->currency_id));

            if (empty($payment_method->settings->tax)) {
                $payment_method->settings->tax = 0;
            }

            if (empty($payment_method->settings->tax_inside)) {
                $payment_method->settings->tax_inside = 0;
            }

            $tax_amount = FinanceCurrency::priceConvert($order->total_price * $payment_method->settings->tax / 100, $payment_method->currency_id, false);
            $tax_inside_amount = FinanceCurrency::priceConvert($order->total_price * $payment_method->settings->tax_inside / 100, $payment_method->currency_id, false);

            if ($tax_amount == 0 and $tax_inside_amount == 0) {
                return false;
            }

            Design::assign('payment_method', $payment_method);
            Design::assign('payment_currency', $payment_currency);
            Design::assign('tax_amount', $tax_amount);
            Design::assign('tax_inside_amount', $tax_inside_amount);


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
}
