<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.1
 *
 */

namespace HugaShop\Models\Finance;

use HugaShop\Models\BaseModel;
use HugaShop\Models\User\User;

class FinancePaymentContractor extends BaseModel
{

     protected $table = 'finance_entity_related';

    protected static $table_fields = [
        'payment_id' =>         ['type' => 'int',            'req' => true],
        'entity_id' =>          ['type' => 'int',           'req' => true],
        'entity_name' =>        ['type' => 'varchar',        'req' => true]
    ];


    public function payment()
    {
        return $this->hasOne(FinancePayment::class, 'id', 'payment_id');
    }


    /**
     * Ищем связь с сущностями
     * @param $id - ID Payment
     */
    public static function getContractor($payment_id)
    {
        $contractor = self::where('payment_id', $payment_id)->first();
        if (!$contractor) {
            return false;
        }
        return FinancePaymentContractor::setContractorName($contractor);
    }


    /**
     * Определяем навзание Контрагента
     * @param $contractor - Object contractor
     */
    public static function setContractorName($contractor)
    {
        $contractor->entity = new \stdClass();

        // Выбрать данные сущности контрагента
        switch ($contractor->entity_name) {
            case 'user':
                $contractor->entity = User::getUser($contractor->entity_id);
                break;
            case 'order':
                $contractor->entity->name = 'Заказ №' . $contractor->entity_id;
                break;
            case 'wh_movement':
                $contractor->entity->name = 'Складское перемещение №' . $contractor->entity_id;
                break;
        }

        return $contractor;
    }


    /**
     * Создание связи с сущностью
     */
    public static function addContractor($payment)
    {
        $payment = (object) $payment;

        $relation = self::getContractor($payment->payment_id);

        if ($relation) {
            if ($payment->entity_name != $relation->entity_name || $payment->entity_id != $relation->entity_id) {
                return self::updateOne(intval($payment->payment_id), $payment);
            }
        } else {
            return self::create($payment);
        }
    }


    /**
     * Удаление связи с сущностью
     * @param $id - ID payment
     */
    public static function deleteContractor($id)
    {
        return self::where('payment_id', $id)->delete() > 0;
    }


    /**
     * Поиск платежа с оплатой заказа
     * fp.type = 1 - Приход (+)
     * @param $id - ID order
     * @param $payment_type - Тип платежа (расход/приход)|(expense/income)|(0/1)|(-/+)
     */
    public static function getOrderPayment($id, $payment_type = null)
    {
        $query = self::with(['payment' => function ($q) use ($payment_type) {
            if (!is_null($payment_type)) {
                if (in_array($payment_type, ['+', 'income'])) {
                    $payment_type = 1;
                } elseif (in_array($payment_type, ['-', 'expense'])) {
                    $payment_type = 0;
                }
                $q->where('type', $payment_type);
            }
        }])
            ->where('entity_id', $id)
            ->where('entity_name', 'order')
            ->first();

        return $query?->payment;
    }


    /**
     * Поиск связи с контрагентом
     * @param int $entity_id
     * @return array
     */
    public static function getContractorPayments(int $entity_id, string $entity_name)
    {
        return self::where('entity_id', $entity_id)
            ->where('entity_name', $entity_name)
            ->get();
    }
}
