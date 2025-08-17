<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 4.0
 *
 */

namespace HugaShop\Models\Order;

use HugaShop\Services\Helper;
use HugaShop\Models\BaseModel;
use HugaShop\Models\Cart\Cart;
use HugaShop\Models\User\User;
use HugaShop\Models\Product\Product;
use HugaShop\Models\Order\OrderPayment;
use HugaShop\Models\Order\OrderDelivery;
use HugaShop\Models\Finance\FinancePayment;
use HugaShop\Models\Order\OrderLabelRelated;
use HugaShop\Models\Finance\FinancePaymentContractor;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Order extends BaseModel
{

    protected static $table_fields = [
        'id' =>                     ['type' => 'int',       'extra' => 'AUTO_INCREMENT'],
        'delivery_id' =>            ['type' => 'int'],
        'delivery_price' =>         ['type' => 'decimal',   'length' => 10.2],
        'delivery_note' =>          ['type' => 'varchar'],
        'delivery_info' =>          ['type' => 'varchar',   'length' => 900],
        'separate_delivery' =>      ['type' => 'tinyint',   'def' => 0],
        'payment_method_id' =>      ['type' => 'int'],
        'status' =>                 ['type' => 'int',       'def' => 0,             'access' => false],
        'paid' =>                   ['type' => 'tinyint',   'def' => 0],
        'closed' =>                 ['type' => 'tinyint',   'def' => 0,             'access' => false],
        'user_id' =>                ['type' => 'int'],
        'name' =>                   ['type' => 'varchar'],
        'email' =>                  ['type' => 'varchar'],
        'phone' =>                  ['type' => 'varchar'],
        'address' =>                ['type' => 'varchar'],
        'comment' =>                ['type' => 'varchar'],
        'note' =>                   ['type' => 'varchar'],
        'token' =>                  ['type' => 'varchar',                           'access' => false],
        'total_price' =>            ['type' => 'decimal',   'length' => 10.2,       'access' => false],
        'profit_price' =>           ['type' => 'decimal',   'length' => 10.2,       'access' => false],
        'payment_price' =>          ['type' => 'decimal',   'length' => 10.2,       'access' => false],
        'discount' =>               ['type' => 'decimal',   'length' => 10.2],
        'coupon_discount' =>        ['type' => 'decimal',   'length' => 10.2],
        'coupon_code' =>            ['type' => 'varchar'],
        'manager_id' =>             ['type' => 'int'],
        'manager_rate' =>           ['type' => 'int',       'def' => 0,             'access' => false],
        'manager_profit' =>         ['type' => 'decimal',   'length' => 10.2,       'access' => false],
        'settings' =>               ['type' => 'text'],
        'date' =>                   ['type' => 'datetime',  'def' => 'CURRENT_TIMESTAMP',   'access' => false],
        'modified' =>               ['type' => 'datetime',  'access' => false]
    ];

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

    public function purchases(): HasMany
    {
        return $this->hasMany(OrderPurchase::class, 'order_id')->orderBy('position');
    }

    public function label_ids(): HasMany
    {
        return $this->hasMany(OrderLabelRelated::class, 'order_id');
    }

    /**
     * Accessor to get array of label IDs
     */
    public function getLabelIdsAttribute(): array
    {
        if ($this->relationLoaded('label_ids')) {
            return $this->getRelation('label_ids')->pluck('label_id')->toArray();
        }

        return $this->label_ids()->pluck('label_id')->toArray();
    }

    public function labels(): BelongsToMany
    {
        return $this->belongsToMany(
            OrderLabel::class,
            OrderLabelRelated::class,   # имя таблицы связей
            'order_id',                 # внешний ключ для текущего order
            'label_id'                  # внешний ключ для связанного label
        )->orderBy('position');
    }

    public function payments(): BelongsToMany
    {
        return $this->belongsToMany(
            FinancePayment::class,
            FinancePaymentContractor::class,
            'entity_id',
            'payment_id'
        )->wherePivot('entity_name', 'order');
    }


    /**
     * Выбрать определенный заказ
     * @param int|string $id
     */
    public static function getOrder(int|string $id, array $join = [])
    {
        $query = self::query();

        if (!empty($join)) {
            $query->with($join);
        }

        if (is_int($id)) {
            $query->where('id', $id);
        } else {
            $query->where('url', $id);
        }

        $order = $query->first();

        if ($order) {
            $order->settings = empty($order->settings) ? new \stdClass() : (object) unserialize($order->settings);
        }

        return $order;
    }


    /**
     * Выбрать список заказов
     * @param array $filter
     * @param array $join = ['payment_method', 'delivery_method', 'purchases' 'labels']
     * @param string $select = count|sum
     */
    public static function getOrders(array $filter = [], array $join = [], ?string $select = null)
    {
        $query = self::query();

        if (!empty($join)) $query->with($join);

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
            $query->whereHas('label_ids', function ($q) use ($filter) {
                $q->where('label_id', $filter['label']);
            });
        }
        if (!empty($filter['product_id'])) {
            $query->whereHas('purchases', function ($q) use ($filter) {
                $q->where('product_id', intval($filter['product_id']));
            });
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

        // Выбираем кол-во
        if ($select === 'count') {
            return $query->count();
        }

        // Выбираем общую стоимость заказов
        elseif ($select === 'sum') {
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
        foreach ($orders as $order) {
            // Преобразуем serialize data в object
            // Если преобразовывать пустую переменную, в обьект добавляется "scalar"
            $order->settings = empty($order->settings) ? new \stdClass() : (object) unserialize($order->settings);
        }

        return $orders->keyBy('id');
    }


    /**
     * Выбираем кол-во заказов
     * @param array $filter
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
        return Order::getOrders($filter, select: 'sum');
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
        OrderLabelRelated::where('order_id', $id)->delete();
        Cart::updateOne($id, ['order_id' => null]);

        return FinancePayment::deleteOrderPayments($id);
    }


    /**
     * Добавляем заказ
     * @param $order
     */
    public static function addOrder(object $order)
    {
        $order->token = Helper::makeToken();
        $order->date = date("Y-m-d H:i:s");

        // Убираем пробелы в номере телефона
        if (!empty($order->phone)) {
            $order->phone = Helper::clearPhoneNummber($order->phone);
        }

        return self::createOne($order);
    }


    /**
     * Фиксируем заказ (принят, выполнен), забираем товары со склада
     * @return int $order_id
     */
    public static function close(int $order_id)
    {

        $order = self::getOrder($order_id, join: [
            'purchases',
            'purchases.product'
        ]);

        if (empty($order)) {
            return false;
        }

        // Если заказ Не был принят, снимаем товары со склада
        if (empty($order->closed)) {

            // Вычисляем общее кол-во покупки. Может быть несколько одинаковых вариантов
            $products_amounts = [];
            foreach ($order->purchases as $purchase) {
                if (isset($products_amounts[$purchase->product_id])) {
                    $products_amounts[$purchase->product_id] += $purchase->amount;
                } else {
                    $products_amounts[$purchase->product_id] = $purchase->amount;
                }
            }

            // Определяем возможность заказа заданого кол-ва
            // Нельзя отнимать кол-во больше чем есть на складе.
            foreach ($products_amounts as $id => $amount) {
                $product = Product::getOne($id);
                if (!is_null($product->stock)) {
                    if (empty($product) || ($product->stock < $amount)) {
                        return false;
                    }
                }
            }

            foreach ($order->purchases as $purchase) {
                if (!empty($purchase->product) and !is_null($purchase->product->stock)) {

                    // Кол-во нужно добавлять/вычетать в SQL запросе. Чтобы не произошла коллизия при одновременных запросах
                    Product::changeAmount($purchase->product->id, -$purchase->amount);
                }
            }

            self::where('id', $order->id)->update(['closed' => 1]);
        }

        return true;
    }


    /**
     * Переводим заказ в открытый (новый или отменен)
     * @param int $order_id
     */
    public static function open(int $order_id)
    {

        $order = self::getOrder($order_id, join: [
            'purchases',
            'purchases.product'
        ]);

        if (empty($order)) {
            return false;
        }

        // Если заказ был принят, возвращаем товары на склад
        if ($order->closed) {
            foreach ($order->purchases as $purchase) {
                if (!empty($purchase->product) && !is_null($purchase->product->stock)) {
                    Product::changeAmount($purchase->product->id, $purchase->amount);
                }
            }
            self::where('id', $order->id)->update(['closed' => 0]);
        }

        return true;
    }


    /**
     * Вычисляем и обновляем общую Стоимость и Прибыль
     * @param int $ordre_id
     * @param bool $modified - update edit date
     * @return $order_id
     */
    public static function updateTotalPrice(int $order_id, bool $modified = true)
    {
        // Get order informatiion
        if (!$order = self::with(['purchases', 'manager', 'manager.group', 'payment_method'])->find($order_id)) {
            return false;
        }

        // Вычисляем комиссию способа доставки
        if (!empty($order->delivery_id)) {
            $delivery_method = OrderDelivery::getOne($order->delivery_id);
            // TODO: Дописать логику
        }

        // Выбираем все товары заказа

        // Выбираем общую стоимость товаров заказа (чистая сумма)
        $order_clean_price = $order->purchases->sum(fn($p) => $p->price * $p->amount);

        // Выбираем общую себестоимость товаров заказов
        $order_cost_price  = $order->purchases->sum(fn($p) => $p->cost_price * $p->amount);

        // Вычисляем стоимость заказа со скидкой и купоном
        $order_discount_price = $order_clean_price * (100 - $order->discount) / 100 - $order->coupon_discount;

        // Выбираем настройки способа оплаты
        if (!empty($order->payment_method_id)) {
            $payment = OrderPayment::getOne($order->payment_method_id);
        }

        // Вычисляем внутренюю комиссию способа оплаты
        $order_payment_fee_inside_price = 0;
        if (!empty($payment->settings->fee_inside)) {
            $order_payment_fee_inside_price = $order_discount_price * $payment->settings->fee_inside / 100;
        }

        // Добавляем платеж за операцию
        if (!empty($payment_settings->fee_fix_inside)) {
            $order_payment_fee_inside_price += $payment->settings->fee_fix_inside;
        }

        // Вычисляем внутренюю сумму налога которую оплачивает продавец
        $order_payment_tax_inside_price = 0;
        if (!empty($payment_settings->tax_inside)) {
            $order_payment_tax_inside_price = $order_discount_price * $payment->settings->tax_inside / 100;
        }

        // Вычисляем общую сумму заказа к оплате
        $order_payment_price = $order_discount_price;

        // Добаляем стоимость доставки
        if (!empty($order->delivery_price) && !$order->separate_delivery) {
            $order_payment_price += $order->delivery_price;
        }

        // Если есть налоги способа оплаты, добавляем к цене
        // Формула: (100-tax%)*PriceWithTax = Price => PriceWithTax = Price/((100-tax%)/100)
        if (!empty($payment_settings->tax)) {
            $order_payment_price = $order_payment_price / ((100 - $payment->settings->tax) / 100);
        }

        // Добавляем комиссию сервиса
        if (!empty($payment_settings->fee)) {
            $order_payment_price = $order_payment_price / ((100 - $payment->settings->fee) / 100);
        }

        // TODO: Если основная валюта без копеек, округлим сумму

        // Вычисляем комиссию менеджера
        // Комиссия вычисляется от чистой стоимости заказа (с учетом скидки)
        // Комиссия менеджера уменьшается пропорционально сделаной им скидки
        // TODO Комиссия менеджера уменьшается на заказы от рекламы
        $manager_profit = 0;
        if (!empty($order->manager_id)) { # если присвоен менеджер
            $manager = User::getUser($order->manager_id);
            if (!empty($manager->group->discount)) {
                $manager_discount = $manager->group->discount;

                // Вычисляем ROI заказа
                // Корректируем Комиссию менеджера
                // TODO: Костыль, требует доработки.
                if ($order_cost_price > 0) {
                    $roi = ($order_discount_price - $order_cost_price) / $order_cost_price * 100;
                    if ($roi < 70) {

                        // Пропорциональное уменьшение % менеджера
                        $manager_discount = $manager->group->discount * $roi / 70;
                    }
                }

                $manager_profit = ($order_discount_price * $manager_discount / 100);
                if ($order_clean_price > 0) {

                    // Реальный % скидки на заказ c учетом купона и дисконта(%)
                    $real_order_discaunt = (1 - $order_discount_price / $order_clean_price) * 100;

                    // Вычет из комиссия менеджера = скидка на заказ % * 2
                    $manager_profit -= $manager_profit * $real_order_discaunt * 2 / 100;
                }

                // Округляем до 0.00
                $manager_profit = round($manager_profit, 2);
            }
        }

        // Вычисляем конечную прибыль от заказа
        // Берем общую сумму заказа (после скидки и купона) и отнимает расходы
        // В расходы включены комиссия менеджера, комиссия платежной системы, комиссия способа доставки(?)
        $order_profit_price = ($order_discount_price - $order_cost_price) - $manager_profit - $order_payment_fee_inside_price - $order_payment_tax_inside_price;

        $data = [
            'total_price'    => $order_discount_price,
            'profit_price'   => $order_profit_price,
            'manager_profit' => $manager_profit,
            'payment_price'  => $order_payment_price,
        ];

        if ($modified) {
            $data['modified'] = date('Y-m-d H:i:s');
        }

        self::updateOne($order->id, $data);

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
