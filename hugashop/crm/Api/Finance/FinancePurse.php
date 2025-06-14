<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 3.1
 *
 */

namespace HugaShop\Api\Finance;

use HugaShop\Api\BaseModel;

class FinancePurse extends BaseModel
{
    public static $table_fields = [
        'id' =>                     ['type' => 'int',       'extra' => 'AUTO_INCREMENT'],
        'name' =>                   ['type' => 'varchar',   'req' => true],
        'comment' =>                ['type' => 'varchar'],
        'amount' =>                 ['type' => 'decimal',   'lenght' => 10.2, 'def' => 0.00],
        'currency_id' =>            ['type' => 'int',       'req' => true],
        'position' =>               ['type' => 'int',       'def' => 0],
        'enabled' =>                ['type' => 'tinyint',   'def' => 0]
    ];

    public function currency()
    {
        return $this->belongsTo(FinanceCurrency::class, 'currency_id');
    }


    /**
     * Выбираем кошельки
     * @param array $filter
     */
    public static function getPurses(array $filter = [])
    {

        return self::getList($filter, order: ['position'], joins: 'currency');
    }


    /**
     * Удаляем кошелек
     * Delete only the purse which has payments
     *
     * @param int|array $id
     */
    public static function deletePurse(int|array $id)
    {
        if (is_array($id)) {
            foreach ($id as $current_id) {
                if (empty(FinancePayment::getPayments(['purse_id' => $current_id]))) {
                    self::deleteOne($current_id);
                }
            }
            return true;
        }

        if (empty(FinancePayment::getPayments(['purse_id' => $id]))) {
            return self::deleteOne($id);
        }

        return false;
    }


    /**
     * Подсчитываем сумму на кошельке
     * @param $payment
     * @param string $type - add/back. back - отмена платежа. add - новый платеж
     */
    public static function changeAmount($payment, string $type = 'add')
    {
        if (!isset($payment->amount) || empty($payment->purse_id)) {
            return false;
        }

        // Знак перед числом
        $s = 1;

        // Если трата, ставим минус
        if ($payment->type == 0) {
            $s = -1;
        }

        // Если отменяет, меняем знак
        if ($type == 'back') {
            $s = -1 * $s;
        }

        return self::where('id', $payment->purse_id)->increment('amount', $s * $payment->amount);
    }


    /**
     * Проверяем остаток на кошельке, сложив все платежи
     * @param int $purse_id
     */
    public static function checkAmount(int $purse_id)
    {
        $result = FinancePayment::query()
            ->where('purse_id', $purse_id)
            ->selectRaw('(SUM(IF(type=1, amount, 0)) - SUM(IF(type=0, amount, 0))) as check_amount')
            ->first();

        return $result?->check_amount;
    }


    /**
     * Общий баланс на кошелках
     * @param int $currency_id
     */
    public static function getTotalAmount(int $currency_id)
    {
        return self::query()->where('currency_id', $currency_id)->sum('amount') ?: 0;
    }
}
