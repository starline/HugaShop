<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.4
 *
 */

namespace HugaShop\Models\Product;

use HugaShop\Models\BaseModel;

class ProductFeatureOption extends BaseModel
{

    public $timestamps = true;

    protected static $table_fields = [
        'id' =>             ['type' => 'int',           'extra' => 'AUTO_INCREMENT'],
        'feature_id' =>     ['type' => 'int',           'req' => true],
        'value' =>          ['type' => 'varchar',       'req' => true],
    ];

    /**
     * Обновление вариантов характеристик
     * @param int $feature_id
     * @param array $options
     */
    public static function updateFeatureOptions(int $feature_id, array $options)
    {
        if (empty($feature_id)) {
            return false;
        }

        // TODO тут нужно добавлять новые option, сохранять измененные, удалять путые и удаленные. 

        return true;
    }
}
