<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 3.1
 *
 * Класс для работы с доставкой
 *
 */

namespace HugaShop\Api\Order;

use HugaShop\Api\Config;
use HugaShop\Api\Helper;
use HugaShop\Api\Database;
use HugaShop\Api\Finance\FinancePurse;
use HugaShop\Api\DatabaseQuery;

class OrderDelivery extends DatabaseQuery
{
    public static $table = [
        'fields' => [
            'id' =>                 ['type' => 'int',        'extra' => 'AUTO_INCREMENT'],
            'name' =>               ['type' => 'varchar',    'req' => true],
            'public_name' =>        ['type' => 'varchar',    'req' => true],
            'description' =>        ['type' => 'text'],
            'module' =>             ['type' => 'varchar'], # NovaPoshta|DeliveryAuto|...
            'settings' =>           ['type' => 'text'],
            'free_from' =>          ['type' => 'decimal',   'lenght' => 10.2],
            'price' =>              ['type' => 'decimal',   'lenght' => 10.2],
            'enabled' =>            ['type' => 'tinyint',   'def' => 0],
            'enabled_public' =>     ['type' => 'tinyint',   'def' => 0],
            'position' =>           ['type' => 'int'],
            'separate_payment' =>   ['type' => 'tinyint',   'def' => 0],
            'finance_purse_id' =>   ['type' => 'int'],
            'comment' =>            ['type' => 'varchar']
        ],
        'join' => [
            FinancePurse::class => ['id' => 'finance_purse_id']
        ],
    ];


    /**
     * Выбираем все способы доставки
     * @param array $filter
     */
    public static function getDeliveryMethods(array $filter = [])
    {
        return self::getList($filter, 'position');
    }


    /**
     * Выбираем способы оплаты для выбранной доставки
     */
    public static function getDeliveryPayments($id)
    {
        if (empty($id)) {
            return false;
        }

        $query = Database::placehold("SELECT payment_method_id FROM __order_delivery_payment WHERE delivery_id=?", intval($id));
        return self::query($query)->results('payment_method_id');
    }


    /**
     * Обновляем способы оплаты для выбранной доставки
     */
    public static function updateDeliveryPayments($id, array $payment_methods_ids)
    {
        $payment_methods_ids = empty($payment_methods_ids) ? [] : $payment_methods_ids;

        $query = Database::placehold("DELETE FROM __order_delivery_payment WHERE delivery_id=?", intval($id));
        self::query($query);

        if (is_array($payment_methods_ids)) {
            foreach ($payment_methods_ids as $p_id) {
                self::query("INSERT INTO __order_delivery_payment SET delivery_id=?, payment_method_id=?", $id, $p_id);
            }
        }
    }


    /**
     * Выбираем модуль доставки
     */
    public static function getDeliveryModules()
    {
        return Helper::getModules(Config::get('delivery_dir'));
    }


    /**
     * Выводим модуль доставки
     * Модуль находиться в src/modules/delivery
     * В Smarty подключается как плагин
     * @param array $params
     */
    public static function getDeliveryModuleHtml(array $params)
    {
        $module_name = preg_replace("/[^A-Za-z0-9]+/", "", $params['module']);
        $ClassName = "HugaShop\\Modules\\Delivery\\{$module_name}\\{$module_name}";
        $form = '';

        if (!empty($module_name) and class_exists($ClassName)) {
            $Module = new $ClassName();
            $form = $Module->checkoutForm($params['order_id'], $params['view_type']);
        }

        return $form;
    }
}
