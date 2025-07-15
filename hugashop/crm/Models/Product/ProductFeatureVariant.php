<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.3
 *
 */

namespace HugaShop\Models\Product;

use HugaShop\Models\BaseModel;
use HugaShop\Models\Localization\Language;

class ProductFeatureVariant extends BaseModel
{

    protected static $table_fields = [
        'id' =>                 ['type' => 'int',       'lenght' => 11, 'extra' => 'AUTO_INCREMENT'],
        'name' =>               ['type' => 'varchar',   'req' => true, 'trans' => true],
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

        $language_code = Language::checkOrGetCode();
        $main_code = Language::getMain()->code;

        // If editing translation only update translation records
        if ($language_code && $language_code !== $main_code) {
            $ids = self::where('feature_id', $feature_id)
                ->orderBy('position')
                ->pluck('id')
                ->toArray();

            foreach ($ids as $index => $id) {
                $name = $variants[$index] ?? '';
                self::updateTranslation($id, $language_code, ['name' => $name]);
            }

            return true;
        }

        // Удаляем старые варианты для основной версии
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
        $query = self::where('feature_id', $feature_id)
            ->orderBy('position');

        $variants = $query->get();

        if ($code = Language::checkOrGetCode() and self::isTranslatable()) {
            self::fillTranslations($variants, $code, merge_fields: false);
        }

        return $variants->pluck('name')->toArray();
    }
}
