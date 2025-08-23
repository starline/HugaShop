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
use HugaShop\Models\Product\ProductFeature;
use HugaShop\Models\Product\ProductCategory;

class ProductCategoryFeature extends BaseModel
{

    protected static $table_fields = [
        'id'            => ['type' => 'int',           'extra' => 'AUTO_INCREMENT'],
        'category_id'   => ['type' => 'int',           'req' => true],
        'feature_id'    => ['type' => 'int',           'req' => true],
    ];

    protected static $table_keys = [
        'feature_id'    => ['column' => ['feature_id'],     'type' => 'index'],
        'category_id'   => ['column' => ['category_id'],    'type' => 'index']

    ];

    public function category()
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    public function feature()
    {
        return $this->belongsTo(ProductFeature::class, 'feature_id');
    }


    /**
     * Get products categories where are features
     * @param $feature_id
     */
    public static function getFeatureCategoryIds(int $feature_id): array
    {
        return self::where('feature_id', $feature_id)
            ->pluck('category_id')
            ->toArray();
    }


    /**
     * Add feature to category
     * @param int $feature_id
     * @param int $category_id
     */
    public static function addFeatureCategory(int $feature_id, int $category_id)
    {
        return self::firstOrCreate([
            'feature_id' => $feature_id,
            'category_id' => $category_id,
        ]);
    }


    /**
     * Update feature categories
     * @param integer $feature_id
     * @param array $categories
     */
    public static function updateFeatureCategories(int $feature_id, array $categories)
    {

        if (empty($feature_id)) {
            return false;
        }

        // Удалим все старые связи
        self::where('feature_id', $feature_id)->delete();

        if (!empty($categories)) {

            // Массовая вставка новых связей
            $insert = collect($categories)->map(fn($category_id) => [
                'feature_id' => $feature_id,
                'category_id' => (int) $category_id,
            ])->all();

            self::insert($insert);

            // Удалим значения из options, если категория товара не входит в список
            ProductOption::where('feature_id', $feature_id)
                ->whereHas('product', function ($query) use ($categories) {
                    $query->whereNotIn('category_id', $categories);
                })
                ->delete();
        } else {

            // Если список пуст, удалим все опции этой характеристики
            ProductOption::where('feature_id', $feature_id)->delete();
        }

        return true;
    }
}
