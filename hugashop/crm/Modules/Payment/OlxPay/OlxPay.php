<?php

namespace HugaShop\Modules\Payment\OlxPay;

use HugaShop\Api\Order\Order;
use HugaShop\Api\Config;
use HugaShop\Api\Design;
use HugaShop\Api\Order\OrderPayment;
use HugaShop\Api\Finance\FinanceCurrency;

class OlxPay
{

    public function checkoutForm($order_id, $view_type)
    {

        if (!empty($order_id)) {
            $order = Order::getOrder((int)$order_id);

            $payment_method =   OrderPayment::getOne($order->payment_method_id);
            $payment_currency = FinanceCurrency::getCurrency(intval($payment_method->currency_id));

            if (empty($payment_method->settings->fee_inside)) {
                $payment_method->settings->fee_inside = 0;
            }

            if (empty($payment_method->settings->fee_fix_inside)) {
                $payment_method->settings->fee_fix_inside = 0;
            }

            // Не учитываем стоимость доставки

            $fee_inside_amount = FinanceCurrency::priceConvert($order->total_price * $payment_method->settings->fee_inside / 100, $payment_method->currency_id);
            $fee_fix_inside_amount = $payment_method->settings->fee_fix_inside;

            if ($fee_inside_amount == 0 and $fee_fix_inside_amount == 0) {
                return false;
            }

            if ($fee_inside_amount > 0 and $fee_fix_inside_amount > 0) {
                $sum_inside = $fee_inside_amount + $fee_fix_inside_amount;
                Design::assign('sum_inside', $sum_inside);
            }

            Design::assign('payment_method', $payment_method);
            Design::assign('payment_currency', $payment_currency);

            Design::assign('fee_inside_amount', $fee_inside_amount);
            Design::assign('fee_fix_inside_amount', $fee_fix_inside_amount);

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
