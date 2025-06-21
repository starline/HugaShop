<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.2
 *
 */

namespace HugaShop\Api\Product;

use HugaShop\Api\BaseModel;

class ProductFeatureVariant extends BaseModel
{

    protected static $table_fields = [
        'id' =>                 ['type' => 'int',       'lenght' => 11, 'extra' => 'AUTO_INCREMENT'],
        'name' =>               ['type' => 'varchar',   'req' => true],
        'feature_id' =>         ['type' => 'int'],
        'position' =>           ['type' => 'int',       'def' => 0]

    ];

    public function feature()
    {
        return $this->belongsTo(ProductFeature::class, 'feature_id');
    }


    /**
     * Обновление вариантов характеристик
     * @param int $feature_id
     * @param array $variants
     */
    public static function updateFeatureVariants(int $feature_id, array $variants)
    {
        if (empty($feature_id)) {
            return false;
        }

        // Удаляем старые варианты
        self::where('feature_id', $feature_id)->delete();

        // Исключавем пустые значения варината
        $filtered = array_filter($variants, fn($v) => !empty($v));

        if (!empty($filtered)) {
            $insertData = [];

            foreach (array_values($filtered) as $position => $variant) {
                $insertData[] = [
                    'feature_id' => $feature_id,
                    'name'       => $variant,
                    'position'   => $position
                ];
            }

            self::insert($insertData);
        }

        return true;
    }


    /**
     * Feature variants
     */
    public static function getFeatureVariants(int $feature_id)
    {
        return self::where('feature_id', $feature_id)
            ->orderBy('position')
            ->pluck('name')
            ->toArray();
    }
}
