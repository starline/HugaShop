<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.2
 *
 */


namespace HugaShop\Addons\GoogleMerchant\Models;

use HugaShop\Addons\BaseAddonModel;
use HugaShop\Addons\GoogleMerchant\Models\GoogleMerchantCategory;

final class GoogleMerchant extends BaseAddonModel
{
    protected static $table_fields = [
        'id' =>             ['type' => 'int',     'extra' => 'AUTO_INCREMENT'],
        'name' =>           ['type' => 'varchar', 'req' => true],
        'label' =>          ['type' => 'varchar'],
        'currency_code' =>  ['type' => 'varchar'],
        'token' =>          ['type' => 'varchar', 'access' => false],
        'sku_id' =>         ['type' => 'tinyint', 'def' => 0],
        'comment' =>        ['type' => 'varchar'],
        'show_out_stock' => ['type' => 'tinyint', 'def' => 0],
        'created' =>        ['type' => 'datetime', 'def' => 'CURRENT_TIMESTAMP'],
        'position' =>       ['type' => 'int',     'def' => 0],
    ];

    public static function deleteOne($id)
    {
        GoogleMerchantCategory::where('pricefeed_id', $id)->delete();
        return parent::deleteOne($id);
    }
}
