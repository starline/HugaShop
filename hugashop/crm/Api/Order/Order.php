<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 3.7
 *
 */

namespace HugaShop\Api\Order;

use HugaShop\Api\Cart\Cart;
use HugaShop\Api\User\User;
use HugaShop\Api\Helper;
use HugaShop\Api\Database;
use Illuminate\Database\Capsule\Manager as Capsule;
use HugaShop\Api\Order\OrderPayment;
use HugaShop\Api\Order\OrderDelivery;
use HugaShop\Api\Finance\FinancePayment;
use HugaShop\Api\BaseModel;
use HugaShop\Api\Product\ProductVariant;

class Order extends BaseModel
{
    public static $table_fields = [
        'id' =>                     ['type' => 'int'],
        'delivery_id' =>            ['type' => 'int'],
        'delivery_price' =>         ['type' => 'decimal',   'lenght' => 10.2],
        'delivery_note' =>          ['type' => 'varchar'],
        'delivery_info' =>          ['type' => 'varchar',   'lenght' => 900],
        'separate_delivery' =>      ['type' => 'tinyint',   'def' => 0],
        'payment_method_id' =>      ['type' => 'int'],
        'status' =>                 ['type' => 'int',       'def' => 0],
        'paid' =>                   ['type' => 'tinyint',   'def' => 0],
        'closed' =>                 ['type' => 'tinyint', 'def' => 0, 'access' => false],
        'user_id' =>                ['type' => 'int'],
        'name' =>                   ['type' => 'varchar'],
        'email' =>                  ['type' => 'varchar'],
        'phone' =>                  ['type' => 'varchar'],
        'address' =>                ['type' => 'varchar'],
        'comment' =>                ['type' => 'varchar'],
        'note' =>                   ['type' => 'varchar'],
        'url' =>                    ['type' => 'varchar', 'access' => false],
        'total_price' =>            ['type' => 'decimal',   'lenght' => 10.2,       'access' => false],
        'profit_price' =>           ['type' => 'decimal',   'lenght' => 10.2,       'access' => false],
        'interest_price' =>         ['type' => 'decimal',   'lenght' => 10.2,       'access' => false],
        'payment_price' =>          ['type' => 'decimal',   'lenght' => 10.2,       'access' => false],
        'discount' =>               ['type' => 'decimal',   'lenght' => 10.2],
        'coupon_discount' =>        ['type' => 'decimal',   'lenght' => 10.2],
        'coupon_code' =>            ['type' => 'varchar'],
        'date' =>                   ['type' => 'datetime',  'def' => 'CURRENT_TIMESTAMP',   'access' => false],
        'modified' =>               ['type' => 'datetime', 'access' => false],
        'manager_id' =>             ['type' => 'int'],
        'settings' =>               ['type' => 'text']
    ];

    protected $with = ['payment_method', 'delivery_method'];

    public function payment_method()
    {
        return $this->belongsTo(OrderPayment::class, 'payment_method_id');
    }

    public function delivery_method()
    {
        return $this->belongsTo(OrderDelivery::class, 'delivery_id');
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }


    /**
     * Выбрать определенный заказ
     * @param int|string $id
     */
    public static function getOrder(int|string|null $id = null, array $join = []): ?self
    {
        if (empty($id)) {
            return null;
        }

        $query = self::query();

        $with = [];
        if (in_array('payment_method', $join)) $with[] = 'payment_method';
        if (in_array('delivery_method', $join)) $with[] = 'delivery_method';
        if (in_array('manager', $join)) $with[] = 'manager';
        if (in_array('user', $join)) $with[] = 'user';
        if (!empty($with)) $query->with($with);

        if (is_int($id)) {
            $order = $query->where('id', $id)->first();
        } else {
            $order = $query->where('url', $id)->first();
        }

        if ($order) {
            $order->settings = empty($order->settings) ? new \stdClass() : (object) unserialize($order->settings);
        }

        return $order;
    }


    /**
     * Выбрать список заказов
     * @param array $filter
     * @param string|bool $select = false|count|sum
     * @param array $join = ['payment_method', 'delivery_method']
     */
    public static function getOrders(array $filter = [], string|bool $select = false, array $join = [])
    {
        $query = self::query();

        if (!empty($filter['payment_method_id'])) {
            $query->where('payment_method_id', $filter['payment_method_id']);
        }
        if (!empty($filter['delivery_id'])) {
            $query->where('delivery_id', $filter['delivery_id']);
        }
        if (isset($filter['paid'])) {
            $query->where('paid', intval($filter['paid']));
        }
        if (isset($filter['status'])) {
            $query->where('status', intval($filter['status']));
        }
        if (!empty($filter['id'])) {
            $query->whereIn('id', (array)$filter['id']);
        }
        if (!empty($filter['user_id'])) {
            $query->where('user_id', intval($filter['user_id']));
        }
        if (!empty($filter['modified_since'])) {
            $query->where('modified', '>', $filter['modified_since']);
        }
        if (!empty($filter['label'])) {
            $query->join('order_label_related as ol', 'ol.order_id', '=', 'order.id');
            $query->where('ol.label_id', $filter['label']);
        }
        if (!empty($filter['product_id'])) {
            $query->join('order_purchase as op', 'op.order_id', '=', 'order.id');
            $query->where('op.product_id', intval($filter['product_id']));
        }

        // Ищем заказ по ID|phone|address|name
        if (!empty($filter['keyword'])) {
            $keywords = explode(' ', $filter['keyword']);
            $query->where(function ($q) use ($keywords) {
                foreach ($keywords as $kw) {
                    $kw = trim($kw);
                    $clean = str_replace('-', '', $kw);
                    $q->orWhere('id', $kw)
                        ->orWhere('name', 'like', "%$kw%")
                        ->orWhereRaw('REPLACE(phone, "-", "") LIKE ?', ["%$clean%"])
                        ->orWhere('address', 'like', "%$kw%");
                }
            });
        }

        $with = [];
        if (in_array('payment_method', $join)) $with[] = 'payment_method';
        if (in_array('delivery_method', $join)) $with[] = 'delivery_method';
        if (in_array('manager', $join)) $with[] = 'manager';
        if (in_array('user', $join)) $with[] = 'user';
        if (!empty($with)) $query->with($with);



        // Выбираем кол-во
        if ($select === 'count') {
            return $query->count();

            // Выбираем общую стоимость заказов
        } elseif ($select === 'sum') {
            return $query->selectRaw('SUM(total_price) as sum_total_price, SUM(profit_price) as sum_profit_price')->first();
        }

        // Pages view
        if (isset($filter['limit']) && $filter['limit'] != 'all') {
            $limit = max(1, intval($filter['limit']));
            $page = isset($filter['page']) ? max(1, intval($filter['page'])) : 1;
            $query->limit($limit)->offset(($page - 1) * $limit);
        }

        $query->orderByDesc('id');

        // Выбираем заказы
        $orders = $query->get();
        foreach ($orders as $o) {
            // Преобразуем json в object
            // Если преобразовывать пустую переменную, в обьект добавляется "scalar"
            $o->settings = empty($o->settings) ? new \stdClass() : (object) unserialize($o->settings);
        }

        return $orders->keyBy('id')->all();
    }



    /**
     * Выбираем кол-во заказов
     * @param array $filter
     * @return int
     */
    public static function getOrdersCount(array $filter = [])
    {
        return Order::getOrders($filter, select: 'count');
    }


    /**
     * Выбираем общую сумму заказов
     * @param array $filter
     * @return object ($sum_total_price, $sum_profit_price)
     */
    public static function getOrdersPrice(array $filter = [])
    {
        return Order::getOrders($filter, 'sum');
    }


    /**
     * Обновление заказа
     * @param int $id
     * @param $order
     * @param bool $modified
     * @return $id - ID заказа
     */
    public static function updateOrder(int $id, object|array  $order, bool $modified = true)
    {
        $order = (object) $order;

        // Убираем пробелы в номере телефона и добавляем +
        if (!empty($order->phone)) {
            $order->phone = Helper::clearPhoneNummber($order->phone);
        }

        if ($modified === true) {
            $order->modified = date("Y-m-d H:i:s");
        }

        return self::updateOne($id, $order);
    }


    /**
     * Удаляем заказ
     * @param int $id
     */
    public static function deleteOrder(int $id)
    {
        if (empty($id)) {
            return false;
        }

        self::where('id', $id)->delete();
        OrderPurchase::where('order_id', $id)->delete();
        Capsule::table('order_label_related')->where('order_id', $id)->delete();
        Cart::updateOne($id, ['order_id' => null]);

        return FinancePayment::deleteOrderPayments($id);
    }


    /**
     * Добавляем заказ
     * @param $order
     */
    public static function addOrder(object $order)
    {
        $order->url = Helper::makeToken();
        $order->date = date("Y-m-d H:i:s");

        // Убираем пробелы в номере телефона
        if (!empty($order->phone)) {
            $order->phone = Helper::clearPhoneNummber($order->phone);
        }

        return self::create((array)$order);
    }


    /**
     * Фиксируем заказ (принят, выполнен), забираем товары со склада
     * @return int $order_id
     */
    public static function close(int $order_id)
    {

        if (empty($order = Order::getOrder(intval($order_id)))) {
            return false;
        }

        // Если заказ Не был принят, снимаем товары со склада
        if (empty($order->closed)) {
            $purchases = OrderPurchase::getPurchases(['order_id' => $order->id]);

            // Вычисляем общее кол-во покупки. Может быть несколько одинаковых вариантов
            $variants_amounts = [];
            foreach ($purchases as $purchase) {
                if (isset($variants_amounts[$purchase->variant_id])) {
                    $variants_amounts[$purchase->variant_id] += $purchase->amount;
                } else {
                    $variants_amounts[$purchase->variant_id] = $purchase->amount;
                }
            }

            // Определяем возможность заказа заданого кол-ва
            // Нельзя отнимать кол-во больше чем есть нна складе.
            foreach ($variants_amounts as $id => $amount) {
                $variant = ProductVariant::getVariant($id);
                if (empty($variant) || ($variant->stock < $amount)) {
                    return false;
                }
            }

            foreach ($purchases as $purchase) {
                $variant = ProductVariant::getVariant($purchase->variant_id);
                if (!empty($variant) and !is_null($variant->stock)) {

                    // Кол-во нужно добавлять/вычетать в SQL запросе. Чтобы не произошла коллизия при одновременных запросах
                    ProductVariant::updateStock($variant->id, -$purchase->amount);
                }
            }

            self::where('id', $order->id)->update(['closed' => 1]);
            return true;
        }

        return true;
    }


    /**
     * Переводим заказ в открытый (новый или отменен)
     * @param int $order_id
     */
    public static function open(int $order_id)
    {

        if (empty($order = self::getOrder($order_id))) {
            return false;
        }

        // Если заказ был принят, возвращаем товары на склад
        if ($order->closed) {
            $purchases = OrderPurchase::getPurchases(['order_id' => $order->id]);
            foreach ($purchases as $purchase) {
                $variant = ProductVariant::getVariant($purchase->variant_id);
                if (!empty($variant) && !is_null($variant->stock)) {

                    // Кол-во нужно добавлять/вычетать в SQL запросе. Чтобы не произошла коллизия при одновременных запросах
                    ProductVariant::updateStock($variant->id, $purchase->amount);
                }
            }
            self::where('id', $order->id)->update(['closed' => 0]);
            return true;
        }

        return true;
    }


    /**
     * Вычисляем и обновляем общую Стоимость и Прибыль
     * @param int $ordre_id
     * @param bool $modified - update edit date
     * @return $order_id
     */
    public static function updateTotalPrice(int $order_id, $modified = true)
    {

        // Get order informatiion
        if (empty($order = Order::getOrder(intval($order_id)))) {
            return false;
        }

        // Вычисляем комиссию способа доставки
        if (!empty($order->delivery_id)) {
            $delivery_method = OrderDelivery::getOne($order->delivery_id);
            // TODO: Дописать логику
        }

        // Выбираем все товары заказа
        $order_purchases = OrderPurchase::getPurchases(array('order_id' => $order->id));

        // Выбираем общую стоимость товаров заказа (чистая сумма)
        $order_clean_price = 0;
        foreach ($order_purchases as $purchase) {
            $order_clean_price += $purchase->price * $purchase->amount;
        }

        // Выбираем общую себестоимость товаров заказов
        $order_cost_price = 0;
        foreach ($order_purchases as $purchase) {
            $order_cost_price += $purchase->cost_price * $purchase->amount;
        }


        // Вычисляем стоимость заказа со скидкой и купоном
        $order_discount_price = $order_clean_price * (100 - $order->discount) / 100 - $order->coupon_discount;
        $set_total_price = Database::placehold("o.total_price = ? ", $order_discount_price);

        // Выбираем настройки способа оплаты
        if (!empty($order->payment_method_id)) {
            $payment_settings = OrderPayment::getPaymentMethodSettings($order->payment_method_id);
        }

        // Вычисляем внутренюю комиссию способа оплаты
        $order_payment_fee_inside_price = 0;
        if (!empty($payment_settings->fee_inside)) {
            $order_payment_fee_inside_price = $order_discount_price * $payment_settings->fee_inside / 100;
        }

        // Добавляем платеж за операцию
        if (!empty($payment_settings->fee_fix_inside)) {
            $order_payment_fee_inside_price += $payment_settings->fee_fix_inside;
        }


        // Вычисляем внутренюю сумму налога которую оплачивает продавец
        $order_payment_tax_inside_price = 0;
        if (!empty($payment_settings->tax_inside)) {
            $order_payment_tax_inside_price = $order_discount_price * $payment_settings->tax_inside / 100;
        }


        // Вычисляем общую сумму заказа к оплате
        $order_payment_price = $order_discount_price;

        // Добаляем стоимость доставки
        if (!empty($order->delivery_price) and !$order->separate_delivery) {
            $order_payment_price += $order->delivery_price;
        }

        // Если есть налоги способа оплаты, добавляем к цене
        // Формула: (100-tax%)*PriceWithTax = Price => PriceWithTax = Price/((100-tax%)/100)
        if (!empty($payment_settings->tax)) {
            $order_payment_price = $order_payment_price / ((100 - $payment_settings->tax) / 100);
        }

        // Добавляем комиссию сервиса
        if (!empty($payment_settings->fee)) {
            $order_payment_price = $order_payment_price / ((100 - $payment_settings->fee) / 100);
        }

        // BUG: Если основная валюта без копеек, округлим сумму

        $set_payment_price = Database::placehold(", o.payment_price = ? ", $order_payment_price);


        // Вычисляем комиссию менеджера
        // Комиссия вычисляется от чистой стоимости заказа (с учетом скидки)
        // Комиссия менеджера уменьшается пропорционально сделаной им скидки
        // BUG Комиссия менеджера уменьшается на заказы от рекламы
        $manager_interest = 0;
        $set_interest  = "";
        if (!empty($order->manager_id)) { # если присвоен менеджер
            $manager = User::getUser($order->manager_id);
            if (!empty($manager->group->discount)) {
                $manager_discount = $manager->group->discount;

                // Вычисляем ROI заказа
                // Корректируем Комиссию менеджера
                // BUG: Костыль, требует доработки.
                if ($order_cost_price > 0) {
                    $roi =  ($order_discount_price - $order_cost_price) / $order_cost_price * 100;
                    if ($roi < 70) {

                        // Пропорциональное уменьшение % менеджера
                        $manager_discount = $manager->group->discount * $roi / 70;
                    }
                }

                $manager_interest = ($order_discount_price * $manager_discount / 100);
                if ($order_clean_price > 0) {

                    // Реальный % скидки на заказ c учетом купона и дисконта(%)
                    $real_order_discaunt = (1 - $order_discount_price / $order_clean_price) * 100;

                    // Вычет из комиссия менеджера = скидка на заказ % * 2
                    $manager_interest_discount =  $manager_interest * $real_order_discaunt * 2 / 100;
                    $manager_interest = $manager_interest - $manager_interest_discount;
                }

                // Округляем до 0.00
                $manager_interest = round($manager_interest, 2);
                $set_interest = Database::placehold(", o.interest_price = ? ", $manager_interest);
            }
        }


        // Вычисляем конечную прибыль от заказа
        // Берем общую сумму заказа (после скидки и купона) и отнимает расходы
        // В расходы включены комиссия менеджера, комиссия платежной системы, комиссия способа доставки(?)
        $order_profit_price = ($order_discount_price - $order_cost_price) - $manager_interest - $order_payment_fee_inside_price - $order_payment_tax_inside_price;
        $set_profit_price = Database::placehold(", o.profit_price = ? ", $order_profit_price);

        $set_modified = "";
        if ($modified) {
            $set_modified = Database::placehold(", modified=? ", date("Y-m-d H:i:s"));
        }

        $query = Database::placehold(
            "UPDATE 
				__order o 
            SET 
                $set_total_price 
                $set_profit_price 
                $set_interest 
                $set_modified 
                $set_payment_price 
			WHERE 
				o.id = ? 
			LIMIT 
				1",
            intval($order->id)
        );

        Order::query($query);
        return $order->id;
    }


    /**
     * Выбираем следующий заказ
     * @param int $id
     * @param int|null $status
     */
    public static function getNextOrder(int $id, ?int $status = null)
    {
        $query = self::query()->where('id', '>', $id);
        if ($status !== null) {
            $query->where('status', $status);
        }
        $next_id = $query->min('id');
        return $next_id ? self::getOrder(intval($next_id)) : false;
    }


    /**
     * Выбираем Предыдущий заказ
     * @param int $id
     * @param int|null $status
     */
    public static function getPrevOrder(int $id, ?int $status = null)
    {
        $query = self::query()->where('id', '<', $id);
        if ($status !== null) {
            $query->where('status', $status);
        }
        $prev_id = $query->max('id');
        return $prev_id ? self::getOrder(intval($prev_id)) : false;
    }
}
