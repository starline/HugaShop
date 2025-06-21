<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 *
 * Для оператора Почта России
 *
 */

namespace HugaShop\Modules\Delivery\PochtaRussia;

use HugaShop\Models\Order\Order;
use HugaShop\Models\Config;
use HugaShop\Models\Design;
use HugaShop\Models\Order\OrderDelivery;

class PochtaRussia
{
    /**
     * Выводим форму
     *
     */
    public function checkoutForm($order_id, $view_type)
    {

        $order = Order::getOrder((int)$order_id);
        $delivery_method = OrderDelivery::getOne($order->delivery_id);

        // Проверим сущестование файла
        if (!empty($view_type)) {
            $file_path = Config::get('delivery_dir') . $delivery_method->module . '/' . $delivery_method->module . '_' . $view_type . '.tpl';
            if (is_file($file_path)) {
                return Design::fetch($file_path);
            }
        }

        return false;
    }
}
