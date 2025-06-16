<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 3.5
 *
 */

namespace HugaShop\Api\Order;

use HugaShop\Api\BaseModel;

class OrderLabel extends BaseModel
{
    public static $table_fields = [
        'id' =>                 ['type' => 'int',       'extra' => 'AUTO_INCREMENT'],
        'name' =>               ['type' => 'varchar',   'req' => true],
        'color' =>              ['type' => 'varchar',   'lenght' => 6],
        'enabled' =>            ['type' => 'tinyint',   'def' => 0],
        'in_filter' =>          ['type' => 'tinyint',   'def' => 0],
        'position' =>           ['type' => 'int',       'def' => 0]
    ];


    /**
     * Get order labels list
     */
    public static function getLabels()
    {
        return self::getList([], 'position');
    }


    /**
     * Удалить метку
     * @param ?int $id
     */
    public static function deleteLabel(?int $id)
    {
        if (empty($id)) {
            return false;
        }

        OrderLabelRelated::where('label_id', $id)->delete();
        return self::deleteOne($id);
    }


    /**
     * Update Order Labels
     * @param int $id
     * @param $labels_ids
     */
    public static function updateOrderLabels(int $id, $labels_ids)
    {
        $labels_ids = (array)$labels_ids;
        OrderLabelRelated::where('order_id', $id)->delete();
        foreach ($labels_ids as $l_id) {
            OrderLabelRelated::query()->insert(['order_id' => $id, 'label_id' => $l_id]);
        }
    }


    public static function addOrderLabels(int $id, $labels_ids)
    {
        $labels_ids = (array)$labels_ids;
        foreach ($labels_ids as $l_id) {
            OrderLabelRelated::query()->insertOrIgnore(['order_id' => $id, 'label_id' => $l_id]);
        }
    }


    public static function deleteOrderLabels(int $id, $labels_ids)
    {
        $labels_ids = (array)$labels_ids;
        foreach ($labels_ids as $l_id) {
            OrderLabelRelated::where('order_id', $id)
                ->where('label_id', $l_id)
                ->delete();
        }
    }
}
