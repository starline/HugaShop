<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 3.2
 *
 * Класс для работы с доставкой
 *
 */

namespace HugaShop\Models\Order;

use HugaShop\Models\Config;
use HugaShop\Services\Helper;
use HugaShop\Models\BaseModel;
use HugaShop\Models\Finance\FinancePurse;
use HugaShop\Models\Order\OrderPaymentDelivery;

class OrderDelivery extends BaseModel
{
    protected static $table_fields = [
        'id' =>                 ['type' => 'int',        'extra' => 'AUTO_INCREMENT'],
        'name' =>               ['type' => 'varchar',    'req' => true],
        'public_name' =>        ['type' => 'varchar',    'req' => true],
        'description' =>        ['type' => 'text'],
        'module' =>             ['type' => 'varchar'],
        'settings' =>           ['type' => 'text'],
        'free_from' =>          ['type' => 'decimal',   'lenght' => 10.2],
        'price' =>              ['type' => 'decimal',   'lenght' => 10.2],
        'enabled' =>            ['type' => 'tinyint',   'def' => 0],
        'enabled_public' =>     ['type' => 'tinyint',   'def' => 0],
        'position' =>           ['type' => 'int'],
        'separate_payment' =>   ['type' => 'tinyint',   'def' => 0],
        'finance_purse_id' =>   ['type' => 'int'],
        'comment' =>            ['type' => 'varchar']
    ];


    public function finance_purse()
    {
        return $this->belongsTo(FinancePurse::class, 'finance_purse_id');
    }

    public function payments()
    {
        return $this->hasMany(OrderPaymentDelivery::class, 'delivery_id');
    }

    public function getPaymentsIdsAttribute()
    {
        return $this->payments->pluck('payment_method_id')->toArray();
    }

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

        return OrderPaymentDelivery::query()
            ->where('delivery_id', $id)
            ->pluck('payment_method_id')
            ->toArray();
    }


    /**
     * Обновляем способы оплаты для выбранной доставки
     */
    public static function updateDeliveryPayments($id, array $payment_methods_ids)
    {
        $payment_methods_ids = empty($payment_methods_ids) ? [] : $payment_methods_ids;
        OrderPaymentDelivery::query()->where('delivery_id', $id)->delete();
        foreach ($payment_methods_ids as $p_id) {
            OrderPaymentDelivery::query()->insert([
                'delivery_id' => $id,
                'payment_method_id' => $p_id
            ]);
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
