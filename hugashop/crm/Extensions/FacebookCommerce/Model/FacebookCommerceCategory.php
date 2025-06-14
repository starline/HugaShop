<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.7
 *
 */

namespace HugaShop\Extensions\FacebookCommerce\Model;

use HugaShop\Extensions\BaseExtensionModel;

class FacebookCommerceCategory extends BaseExtensionModel
{
    public static $table_fields = [
        'pricefeed_id' =>       ['type' => 'int'],
        'category_id' =>        ['type' => 'int']
    ];


    /**
     * Выбиарем категории в которых есть характеристика
     * @param int $pricefeed_id
     */
    public static function getCategoriesIds(int $pricefeed_id)
    {
        return self::getList(['pricefeed_id' => $pricefeed_id], select: 'category_id');
    }


    /**
     * Устанавливаем варинат в прайслист
     * @param int $pricefeed_id
     * @param int $pricefeed_id
     */
    public static function setCategories(int $pricefeed_id, array $category_ids = [])
    {

        // Delete all pricefeeds
        self::where('pricefeed_id', $pricefeed_id)->delete();

        if (!empty($category_ids)) {
            foreach ($category_ids as $cat_id) {
                if (!empty($cat_id)) {
                    self::insert(['pricefeed_id' => $pricefeed_id, 'category_id' => $cat_id]);
                }
            }
        }

        return true;
    }
}
