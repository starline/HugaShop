<?php

namespace HugaShop\Modules\Payment\CdekPay;

use HugaShop\Modules\Payment\PaymentInterface;

use HugaShop\Models\Order\Order;
use HugaShop\Services\Config;
use HugaShop\Services\Design;
use HugaShop\Models\Order\OrderPayment;
use HugaShop\Models\Finance\FinanceCurrency;

class CdekPay implements PaymentInterface
{
    public function checkoutForm($order_id, $view_type)
    {

        if (!empty($order_id)) {

            $order = Order::getOrder((int)$order_id);
            $payment_method = OrderPayment::getOne($order->payment_method_id);
            $payment_currency = FinanceCurrency::getCurrency(intval($payment_method->currency_id));

            $final_price = $order->total_price;

            // Учитываем стоимость доставки
            if ($order->separate_delivery == 0 and !empty($order->delivery_price)) {
                $final_price += $order->delivery_price;
            }

            $tax_amount = FinanceCurrency::priceConvert(($final_price / ((100 - $payment_method->settings->tax) / 100)) - $final_price, $payment_method->currency_id, false);
            $fee_amount = FinanceCurrency::priceConvert((($tax_amount + $final_price) / ((100 - $payment_method->settings->fee) / 100)) - ($tax_amount + $final_price), $payment_method->currency_id, false);

            if ($tax_amount > 0 and $fee_amount > 0) {
                $sum_fee_tax = $tax_amount + $fee_amount;
                Design::assign('sum_fee_tax', $sum_fee_tax);
            }

            $fee_inside_amount = FinanceCurrency::priceConvert($final_price * $payment_method->settings->fee_inside / 100, $payment_method->currency_id, false);
            $tax_inside_amount = FinanceCurrency::priceConvert(($final_price - $fee_inside_amount) * $payment_method->settings->tax_inside / 100, $payment_method->currency_id, false);

            if ($fee_inside_amount > 0 and $tax_inside_amount > 0) {
                $sum_inside = $fee_inside_amount + $tax_inside_amount;
                Design::assign('sum_inside', $sum_inside);
            }

            Design::assign('tax_amount', $tax_amount);
            Design::assign('tax_inside_amount', $tax_inside_amount);
            Design::assign('fee_amount', $fee_amount);
            Design::assign('fee_inside_amount', $fee_inside_amount);
            Design::assign('payment_method', $payment_method);
            Design::assign('payment_currency', $payment_currency);

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

    public function callback(?string $order_url = null, ?string $form_type = null)
    {
        return false;
    }
}
