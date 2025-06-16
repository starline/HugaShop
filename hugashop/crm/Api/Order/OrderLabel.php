<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 3.4
 *
 */

namespace HugaShop\Api\Order;

use Illuminate\Database\Capsule\Manager as Capsule;
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

        Capsule::table('order_label_related')->where('label_id', $id)->delete();
        return self::deleteOne($id);
    }


    /**
     * Get current order label
     * @param int|array $order_id
     */
    public static function getOrderLabels(int|array $order_id = [])
    {
        if (empty($order_id)) {
            return [];
        }

        return Capsule::table('order_label as ol')
            ->select('ol.*', 'olr.order_id')
            ->leftJoin('order_label_related as olr', 'olr.label_id', '=', 'ol.id')
            ->whereIn('olr.order_id', (array) $order_id)
            ->orderBy('ol.position')
            ->get()
            ->all();
    }


    /**
     * Update Order Labels
     * @param int $id
     * @param $labels_ids
     */
    public static function updateOrderLabels(int $id, array $labels_ids)
    {
        OrderLabel::where('order_id', $id)->delete();
        foreach ($labels_ids as $l_id) {
            OrderLabel::create(['order_id' => $id, 'label_id' => $l_id]);
        }
    }


    public static function addOrderLabels(int $id, $labels_ids)
    {
        $labels_ids = (array)$labels_ids;
        foreach ($labels_ids as $l_id) {
            Capsule::table('order_label_related')->insertOrIgnore(['order_id' => $id, 'label_id' => $l_id]);
        }
    }


    public static function deleteOrderLabels(int $id, $labels_ids)
    {
        $labels_ids = (array)$labels_ids;
        foreach ($labels_ids as $l_id) {
            Capsule::table('order_label_related')
                ->where('order_id', $id)
                ->where('label_id', $l_id)
                ->delete();
        }
    }
}
