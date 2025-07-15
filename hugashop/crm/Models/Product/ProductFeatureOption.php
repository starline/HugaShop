<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.5
 *
 */

namespace HugaShop\Models\Product;

use HugaShop\Models\BaseModel;

class ProductFeatureOption extends BaseModel
{

    public $timestamps = true;

    protected static $table_fields = [
        'id' =>             ['type' => 'int',      'extra' => 'AUTO_INCREMENT'],
        'feature_id' =>     ['type' => 'int',      'req' => true],
        'value' =>          ['type' => 'varchar',  'req' => true,   'trans' => true],
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

        // Prepare incoming values
        $values = array_unique(array_filter(array_map('trim', $options), fn($v) => $v !== ''));

        $keep_ids = [];

        foreach ($values as $value) {
            $option = self::firstOrCreate([
                'feature_id' => $feature_id,
                'value'      => $value,
            ]);

            $keep_ids[] = $option->id;
        }

        if (!empty($keep_ids)) {
            self::where('feature_id', $feature_id)
                ->whereNotIn('id', $keep_ids)
                ->delete();
        } else {
            self::where('feature_id', $feature_id)->delete();
        }

        return true;
    }
}
