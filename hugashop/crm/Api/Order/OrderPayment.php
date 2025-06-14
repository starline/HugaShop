<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 3.5
 *
 */

namespace HugaShop\Api\Order;

use HugaShop\Api\Config;
use HugaShop\Api\Helper;
use HugaShop\Api\Database;
use HugaShop\Api\Finance\FinancePurse;
use HugaShop\Api\DatabaseQuery;
use HugaShop\Api\Finance\FinanceCurrency;

class OrderPayment extends DatabaseQuery
{
    public static $table = [
        'fields' => [
            'id' =>                     ['type' => 'int',           'lenght' => 11,       'extra' => 'AUTO_INCREMENT'],
            'name' =>                   ['type' => 'varchar',       'lenght' => 255,      'required' => true],
            'public_name' =>            ['type' => 'varchar',       'lenght' => 255,      'required' => true],
            'enabled' =>                ['type' => 'tinyint'],
            'enabled_public' =>         ['type' => 'tinyint'],
            'currency_id' =>            ['type' => 'int',           'lenght' => 11],
            'comment' =>                ['type' => 'varchar',       'lenght' => 255],
            'module' =>                 ['type' => 'varchar',       'lenght' => 255], # FopUa|BankCard|...
            'description' =>            ['type' => 'text'],
            'finance_purse_id' =>       ['type' => 'int',           'lenght' => 11],
            'settings' =>               ['type' => 'text'],
            'position' =>               ['type' => 'int',           'lenght' => 11, 'def' => 0]
        ],
        'join' => [
            FinancePurse::class => ['id' => 'finance_purse_id'],
            FinanceCurrency::class => ['id' => 'currency_id']
        ]
    ];


    /**
     * Get payments methods
     * @param array $filter
     */
    public static function getPaymentMethods(array $filter = array())
    {
        $alias = self::getAlias();
        $SELECT = self::makeSelect();

        $WHERE = '';
        if (!empty($filter['delivery_id'])) {
            $WHERE .= Database::placehold('AND id in (SELECT payment_method_id FROM __order_delivery_payment dp WHERE dp.delivery_id=?)', intval($filter['delivery_id']));
        }

        if (!empty($filter['enabled'])) {
            $WHERE .= Database::placehold(" AND `$alias`.enabled=?", intval($filter['enabled']));
        }

        if (!empty($filter['enabled_public'])) {
            $WHERE .= Database::placehold(" AND `$alias`.enabled_public=?", intval($filter['enabled_public']));
        }

        $query =
            "SELECT
			 	$SELECT
			FROM 
				__order_payment `$alias`
			WHERE 
				1 
				$WHERE
			ORDER BY 
				`$alias`.position
			";

        return self::query($query)->results();
    }


    /**
     * Выбираем настройки способа оплаты
     * @param int $method_id
     */
    public static function getPaymentMethodSettings(int $id)
    {
        return self::select('settings')->whereId($id)->getResult('settings');
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
        if (empty($id)) {
            return false;
        }

        $query = Database::placehold("SELECT delivery_id FROM __order_delivery_payment WHERE payment_method_id=?", intval($id));
        return self::query($query)->results('delivery_id');
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

        // Удаляем ранее установленные настройки
        $query = Database::placehold("DELETE FROM __order_delivery_payment WHERE payment_method_id=?", $id);
        self::query($query);

        if (is_array($deliveries_ids)) {
            foreach ($deliveries_ids as $d_id) {
                self::query("INSERT INTO __order_delivery_payment SET payment_method_id=?, delivery_id=?", $id, $d_id);
            }
        }
        return true;
    }
}
