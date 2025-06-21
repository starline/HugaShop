<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.0
 *
 */

namespace HugaShop\Extensions\YandexMerchant\Model;

use HugaShop\Extensions\BaseExtensionModel;
use HugaShop\Extensions\YandexMerchant\Model\YandexMerchantCategory;

final class YandexMerchant extends BaseExtensionModel
{
    protected static $table_fields = [
        'id' =>                 ['type' => 'int',     'extra' => 'AUTO_INCREMENT'],
        'name' =>               ['type' => 'varchar', 'req' => true],
        'currency_code' =>      ['type' => 'varchar'],
        'token' =>              ['type' => 'varchar', 'access' => false],
        'sku_id' =>             ['type' => 'tinyint', 'def' => 0],
        'comment' =>            ['type' => 'varchar'],
        'show_out_stock' =>     ['type' => 'tinyint', 'def' => 0],
        'created' =>            ['type' => 'datetime', 'def' => 'CURRENT_TIMESTAMP'],
        'position' =>           ['type' => 'int',     'def' => 0],
    ];

    public static function deleteOne($id)
    {
        YandexMerchantCategory::where('pricefeed_id', $id)->delete();
        return parent::deleteOne($id);
    }
}
