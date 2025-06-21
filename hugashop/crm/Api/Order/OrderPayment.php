<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 3.5
 *
 */

namespace HugaShop\Api\Order;

use stdClass;
use HugaShop\Api\Config;
use HugaShop\Api\Helper;
use HugaShop\Api\BaseModel;
use HugaShop\Api\Finance\FinancePurse;
use HugaShop\Api\Finance\FinanceCurrency;

class OrderPayment extends BaseModel
{
    protected static $table_fields = [
        'id' =>                     ['type' => 'int',           'lenght' => 11,       'extra' => 'AUTO_INCREMENT'],
        'name' =>                   ['type' => 'varchar',       'lenght' => 255,      'required' => true],
        'public_name' =>            ['type' => 'varchar',       'lenght' => 255,      'required' => true],
        'enabled' =>                ['type' => 'tinyint'],
        'enabled_public' =>         ['type' => 'tinyint'],
        'comment' =>                ['type' => 'varchar',       'lenght' => 255],
        'module' =>                 ['type' => 'varchar',       'lenght' => 255],
        'description' =>            ['type' => 'text'],
        'finance_purse_id' =>       ['type' => 'int',           'lenght' => 11],
        'currency_id' =>            ['type' => 'int',           'lenght' => 11],
        'settings' =>               ['type' => 'text'],
        'position' =>               ['type' => 'int',           'lenght' => 11, 'def' => 0]
    ];

    public function finance_purse()
    {
        return $this->belongsTo(FinancePurse::class, 'finance_purse_id');
    }

    public function currency()
    {
        return $this->belongsTo(FinanceCurrency::class, 'currency_id');
    }

    public function deliveries()
    {
        return $this->hasMany(OrderPaymentDelivery::class, 'payment_method_id');
    }

    public function getDeliveriesIdsAttribute()
    {
        return $this->deliveries->pluck('delivery_id')->toArray();
    }


    public static function getPaymentMethod(int $payment_method_id)
    {
        $payment_method = OrderPayment::getOne($payment_method_id);
        $payment_method->settings = $payment_method->settings ? (object) unserialize($payment_method->settings) : new stdClass();
        return $payment_method;
    }


    /**
     * Get payments methods
     * @param array $filter
     */
    public static function getPaymentMethods(array $filter = array(), array $join = [])
    {
        $query = self::query();

        if (!empty($filter['delivery_id'])) {
            $deliveryId = $filter['delivery_id'];
            $query->whereIn('id', function ($q) use ($deliveryId) {
                $q->select('payment_method_id')
                    ->from('order_delivery_payment')
                    ->where('delivery_id', $deliveryId);
            });
        }

        if (in_array('currency', $join))  $with[] = 'currency';
        if (!empty($with)) $query->with($with);

        if (isset($filter['enabled'])) {
            $query->where('enabled', intval($filter['enabled']));
        }

        if (isset($filter['enabled_public'])) {
            $query->where('enabled_public', intval($filter['enabled_public']));
        }

        return $query->orderBy('position')->get();
    }


    /**
     * Выбираем настройки способа оплаты
     * @param int $method_id
     */
    public static function getPaymentMethodSettings(int $id)
    {
        return optional(self::find($id))->settings;
    }


    /**
     * Выбираем модули доставки
     * Переменные в файле settings.yaml
     * tax - % налога, платит покупатель, будет включен в общую стоимось заказа
     * tax_inside - % налога, платит продавец, будет вычтен из общей стоимости заказа
     * fee - % комиссия сервиса, платит продавец, будет вычтен из обшей стоимости заказа
     * fee_inside - % комиссия сервиса, платит продавец, будет вычтена из обзей стоимости заказа
     * fee_fix_inside - фиксированый платеж за сервис, платит продавец, будет вычтена из общей стоимости заказа
     */
    public static function getPaymentModules()
    {
        return Helper::getModules(Config::get('payment_dir'));
    }


    /**
     * Выводим форму оплаты
     * Модуль оплаты находиться в modules/payments
     * В Smarty подключается как плагин
     * @param array $params
     */
    public static function getPaymentModuleHtml(array $params)
    {
        $module_name = preg_replace("/[^A-Za-z0-9]+/", "", $params['module']);
        $ClassName = "HugaShop\\Modules\\Payment\\{$module_name}\\{$module_name}";
        $form = '';

        if (!empty($module_name) and class_exists($ClassName)) {
            $Module = new $ClassName();
            $form = $Module->checkoutForm($params['order_id'], $params['view_type']);
        }

        return $form;
    }


    /**
     * Get payment delivert methods
     * @param int $id
     */
    public static function getPaymentDeliveries(int $id)
    {
        return OrderPaymentDelivery::query()
            ->where('payment_method_id', $id)
            ->pluck('delivery_id')
            ->toArray();
    }


    /**
     * Update payment module settings
     * @return int $id
     * @return array $settings
     */
    public static function updatePaymentSettings(int $id, array $settings)
    {
        self::updateOne($id, ['settings' => $settings]);
    }


    /**
     * Устанавливаем доступные способы доставки для платежа
     * @param int $id - ID способа оплаты
     * @param array $deliveries_ids - array(ID) способов доставки
     */
    public static function updatePaymentDeliveries(int $id, array $deliveries_ids)
    {

        OrderPaymentDelivery::query()->where('payment_method_id', $id)->delete();
        foreach ($deliveries_ids as $d_id) {
            OrderPaymentDelivery::insert([
                'payment_method_id' => $id,
                'delivery_id' => $d_id
            ]);
        }
        return true;
    }
}
