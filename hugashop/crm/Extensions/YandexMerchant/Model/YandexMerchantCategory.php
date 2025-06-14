<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.2
 *
 */

namespace HugaShop\Extensions\YandexMerchant\Model;

use HugaShop\Extensions\BaseExtensionModel;

class YandexMerchantCategory extends BaseExtensionModel
{

    public static $table_fields = [
        'pricefeed_id' => ['type' => 'int'],
        'category_id'  => ['type' => 'int'],
    ];


    public static function getCategoriesIds(int $pricefeed_id)
    {
        return self::getList(['pricefeed_id' => $pricefeed_id], select: 'category_id');
    }


    public static function setCategories(int $pricefeed_id, array $category_ids = [])
    {
        self::query()->where('pricefeed_id', $pricefeed_id)->delete();

        if (!empty($category_ids)) {
            foreach ($category_ids as $cat_id) {
                if (!empty($cat_id)) {
                    self::query()->insert(['pricefeed_id' => $pricefeed_id, 'category_id' => $cat_id]);
                }
            }
        }

        return true;
    }
}
