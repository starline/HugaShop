<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.4
 *
 */

namespace HugaShop\Extensions\FacebookCommerce\Models;

use HugaShop\Extensions\BaseExtensionModel;

final class FacebookCommerce extends BaseExtensionModel
{
    protected static $table_fields = [
        'id' =>                 ['type' => 'int',           'extra' => 'AUTO_INCREMENT'],
        'name' =>               ['type' => 'varchar',       'req' => true],
        'label' =>              ['type' => 'varchar'],
        'currency_code' =>      ['type' => 'varchar'],
        'token' =>              ['type' => 'varchar',       'access' => false],
        'sku_id' =>             ['type' => 'tinyint',       'def' => 0],
        'comment' =>            ['type' => 'varchar'],
        'show_out_stock' =>     ['type' => 'tinyint',       'def' => 0],
        'created' =>            ['type' => 'datetime',      'def' => 'CURRENT_TIMESTAMP'],
        'position' =>           ['type' => 'int',           'def' => 0]
    ];

    /**
     * Delete
     * @param $id
     */
    public static function deleteOne($id)
    {
        FacebookCommerceCategory::where('pricefeed_id', $id)->delete();
        return parent::deleteOne($id);
    }
}
