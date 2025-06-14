<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 3.4
 *
 */

namespace HugaShop\Api\Order;

use HugaShop\Api\Database;
use HugaShop\Api\DatabaseQuery;

class OrderLabel extends DatabaseQuery
{
    public static $table = [
        'fields' => [
            'id' =>                 ['type' => 'int',       'extra' => 'AUTO_INCREMENT'],
            'name' =>               ['type' => 'varchar',   'req' => true],
            'color' =>              ['type' => 'varchar',   'lenght' => 6],
            'enabled' =>            ['type' => 'tinyint',   'def' => 0],
            'in_filter' =>          ['type' => 'tinyint',   'def' => 0],
            'position' =>           ['type' => 'int',       'def' => 0]
        ]
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

        // Удаляем сязи с заказами
        $query = Database::placehold("DELETE FROM __order_label_related WHERE label_id=?", intval($id));

        if (self::query($query)) {

            // Удаляем метку
            $query = Database::placehold("DELETE FROM __order_label WHERE id=? LIMIT 1", intval($id));
            return self::query($query);
        }

        return false;
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

        $alias = self::getAlias();
        $SELECT = self::makeSelect();

        $label_id_filter = Database::placehold('AND olr.order_id in(?@)', (array)$order_id);

        $query = Database::placehold(
            "SELECT 
                $SELECT,
                olr.order_id as order_id
			FROM 
                __order_label `$alias`
            LEFT JOIN 
                __order_label_related olr ON olr.label_id = `$alias`.id 
			WHERE 
				1 
				$label_id_filter 
			ORDER BY 
                `$alias`.position"
        );

        return self::query($query)->results();
    }


    /**
     * Update Order Labels
     * @param int $id
     * @param $labels_ids
     */
    public static function updateOrderLabels(int $id, $labels_ids)
    {
        $labels_ids = (array)$labels_ids;
        $query = Database::placehold("DELETE FROM __order_label_related WHERE order_id=?", intval($id));
        self::query($query);
        if (is_array($labels_ids)) {
            foreach ($labels_ids as $l_id) {
                self::query("INSERT INTO __order_label_related SET order_id=?, label_id=?", $id, $l_id);
            }
        }
    }


    public static function addOrderLabels(int $id, $labels_ids)
    {
        $labels_ids = (array)$labels_ids;
        if (is_array($labels_ids)) {
            foreach ($labels_ids as $l_id) {
                self::query("INSERT IGNORE INTO __order_label_related SET order_id=?, label_id=?", $id, $l_id);
            }
        }
    }


    public static function deleteOrderLabels(int $id, $labels_ids)
    {
        $labels_ids = (array)$labels_ids;
        if (is_array($labels_ids)) {
            foreach ($labels_ids as $l_id) {
                self::query("DELETE FROM __order_label_related WHERE order_id=? AND label_id=?", $id, $l_id);
            }
        }
    }
}
