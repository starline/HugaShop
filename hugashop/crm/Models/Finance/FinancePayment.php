<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 3.3
 *
 */

namespace HugaShop\Models\Finance;

use HugaShop\Models\Image;
use HugaShop\Models\BaseModel;
use HugaShop\Models\User\User;

class FinancePayment extends BaseModel
{
    protected static $table_fields = [
        'id' =>                     ['type' => 'int',       'extra' => 'AUTO_INCREMENT'],
        'purse_id' =>               ['type' => 'int'],
        'finance_category_id' =>    ['type' => 'int'],
        'type' =>                   ['type' => 'int'],      # Don't set tinyint, if empty set 0
        'purse_amount' =>           ['type' => 'decimal',   'lenght' => 10.2,   'def' => 0.00,                  'access' => false],
        'amount' =>                 ['type' => 'decimal',   'lenght' => 10.2,   'def' => 0.00],
        'currency_amount' =>        ['type' => 'decimal',   'lenght' => 10.2,   'def' => 0.00],
        'currency_rate' =>          ['type' => 'decimal',   'lenght' => 10.4,   'def' => 1.0000],
        'comment' =>                ['type' => 'varchar'],
        'manager_id' =>             ['type' => 'int', 'access' => false],
        'date' =>                   ['type' => 'datetime', 'def' => 'CURRENT_TIMESTAMP',   'access' => false],
        'related_payment_id' =>     ['type' => 'int'],
        'verified' =>               ['type' => 'tinyint', 'def' => 0],
        'verified_date' =>          ['type' => 'datetime', 'access' => false],
        'verified_user_id' =>       ['type' => 'int', 'access' => false]
    ];

    public function purse()
    {
        return $this->belongsTo(FinancePurse::class, 'purse_id');
    }

    public function category()
    {
        return $this->belongsTo(FinanceCategory::class, 'finance_category_id');
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function verifiedUser()
    {
        return $this->belongsTo(User::class, 'verified_user_id');
    }

    public function contractor()
    {
        return $this->hasOne(FinancePaymentContractor::class, 'payment_id');
    }

    public function images()
    {
        return $this->hasMany(Image::class, 'entity_id')
            ->where('entity_name', 'payment')
            ->orderBy('position');
    }

    /**
     * Выбираем платеди
     * @param $filter
     * @param $count
     */
    public static function getPayments($filter = [], $count = false, $join = [])
    {
        $query = self::with([
            'purse.currency',
            'category'
        ]);

        if (!empty($filter['keyword'])) {
            foreach (explode(' ', $filter['keyword']) as $kw) {
                $kw = trim($kw);
                $query->where('comment', 'like', "%{$kw}%");
            }
        }

        if (isset($filter['purse_id'])) {
            $query->whereIn('purse_id', (array)$filter['purse_id']);
        }

        if (isset($filter['category_id'])) {
            $query->where('fc.id', (int)$filter['category_id']);
        }

        if (isset($filter['payments_type'])) {
            if ($filter['payments_type'] == 'plus' || $filter['payments_type'] == 'income') {
                $query->where('type', 1)->whereNull('related_payment_id');
            } elseif ($filter['payments_type'] == 'minus' || $filter['payments_type'] == 'expense') {
                $query->where('type', 0)->whereNull('related_payment_id');
            } elseif ($filter['payments_type'] == 'transfer') {
                $query->whereNotNull('related_payment_id');
            }
        }

        $query->orderBy('date', 'desc');

        if ($count) {
            return $query->count();
        }

        if (isset($filter['limit']) && $filter['limit'] !== 'all') {
            $limit = max(1, (int)$filter['limit']);
            $page = max(1, (int)($filter['page'] ?? 1));
            $query->limit($limit)->offset(($page - 1) * $limit);
        }

        $payments = $query->get()->keyBy('id')->all();

        return $payments;
    }


    /**
     * Выбираем кол-во платежей
     * @param $filter
     */
    public static function countPayments($filter = [])
    {
        return self::getPayments($filter, true);
    }


    /**
     * Выбираем платеж
     * Дополнительно выбираем валюту currency_sign
     * @param int $id
     */
    public static function getPayment(int $id)
    {
        return self::with([
            'purse.currency',
            'category'
        ])
            ->find($id);
    }


    /**
     * Добавляем платеж
     * @param $payment
     */
    public static function addPayment($payment)
    {

        if (empty($payment->date)) {
            $payment->date = date("Y-m-d H:i:s");
        }

        $payment->currency_rate = $payment->currency_rate ?? 1;
        $payment->currency_amount = $payment->amount * $payment->currency_rate;

        // TODO: сюда нужно добавить добавление связанного платежа
        FinancePurse::changeAmount($payment, 'add');

        // Select purse amount after update
        $purse = FinancePurse::getOne($payment->purse_id);
        $payment->purse_amount = $purse->amount;

        return self::createOne($payment)->id;
    }


    /**
     * Обновляем платеж
     * Умеет сменять кошельки
     * @param int $id
     * @param array|object $payment
     */
    public static function updatePayment(int $id, array|object $payment)
    {
        $payment = (object)$payment;

        // Если задана суммма платежа, корректируем остаток на кошельке
        if (isset($payment->amount)) {

            $old_payment = self::getPayment($id);
            if (empty($old_payment)) { # payment is not exist
                return false;
            }

            // Если не задан тип платежа (это перевод), сохраняем ранее установленый
            if (!isset($payment->type) and isset($old_payment->type)) {
                $payment->type = $old_payment->type;
            }

            // Обновляем amount
            FinancePurse::changeAmount($old_payment, 'back');
            FinancePurse::changeAmount($payment, 'add');

            // Select purse amount after update if current amount differs from previous amount
            if (!empty($payment->purse_id) and !empty($purse = FinancePurse::getOne($payment->purse_id)) and $payment->amount != $old_payment->amount) {
                $payment->purse_amount = $purse->amount;
            }

            $payment->currency_rate = $payment->currency_rate ?? 1;
            $payment->currency_amount = $payment->amount * $payment->currency_rate;
        }

        // Если верефицируем платеж, устанавливаем дату и пользователя
        if (!empty($payment->verified) and !empty($payment->verified_user_id)) {
            $payment->verified_date = date("Y-m-d H:i:s");
        }

        // Обновляем платеж
        return self::updateOne($id, $payment);
    }


    /**
     * Удалить платеж
     * Пересчитываем остаток на кошельке
     * Удаление связи с контрагентом
     * @param int $id - ID payment
     */
    public static function deletePayment(int $id)
    {
        if (empty($id)) {
            return false;
        }

        $payment = self::getPayment($id);
        FinancePurse::changeAmount($payment, 'back');

        if (self::deleteOne($id)) {
            FinancePaymentContractor::deleteContractor($id);
            return true;
        }

        return false;
    }


    /**
     * Поиск платежа с оплатой заказа
     * @param $id - ID order
     * @param $payment_type - Тип платежа (расход/приход)|(expense/income)|(0/1)|(-/+)
     */
    public static function getOrderPayment(int $order_id, $payment_type = null)
    {

        $query = self::query()
            ->with('contractor')
            ->whereHas('contractor', function ($q) use ($order_id) {
                $q->where('entity_name', 'order')
                    ->where('entity_id', $order_id);
            });

        if (!is_null($payment_type)) {
            if (in_array($payment_type, ['+', 'income'])) {
                $query->where('type', 1);
            } elseif (in_array($payment_type, ['-', 'expense'])) {
                $query->where('type', 0);
            }
        }

        return $query->first();
    }


    /**
     * Удаление платежей заказа
     * @param int $order_id - ID order
     */
    public static function deleteOrderPayments(int $order_id)
    {
        foreach (self::getOrderPayments($order_id) as $payment) {
            if (!self::deletePayment($payment->payment_id)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Поиск связи с пользователем
     * Применяется для отображения статистики Пользователя
     * @param $id - ID user
     */
    public static function getUserPayments($id)
    {
        return FinancePaymentContractor::getContractorPayments($id, 'user');
    }


    /**
     * Поиск связи с поставкой
     * @param $id - ID warehouse
     */
    public static function getWarehousePayments($id)
    {
        return FinancePaymentContractor::getContractorPayments($id, 'wh_movement');
    }


    /**
     * Поиск связи с заказом
     * @param int $id ID order
     */
    public static function getOrderPayments(int $id)
    {
        return FinancePaymentContractor::getContractorPayments($id, 'order');
    }
}
