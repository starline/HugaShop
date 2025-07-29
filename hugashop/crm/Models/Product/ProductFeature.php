<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.6
 *
 */

namespace HugaShop\Models\Product;

use HugaShop\Models\BaseModel;
use HugaShop\Models\Product\ProductFeatureOption;

class ProductFeature extends BaseModel
{

    public $timestamps = true;
    protected static $table_fields = [
        'id'            => ['type' => 'int',        'extra' => 'AUTO_INCREMENT'],
        'name'          => ['type' => 'varchar',    'req' => true, 'trans' => true, 'search' => true],
        'in_filter'     => ['type' => 'tinyint',    'def' => 0],
        'position'      => ['type' => 'int',        'def' => 0]
    ];

    public function options()
    {
        return $this->hasMany(ProductFeatureOption::class, 'feature_id');
    }


    /**
     * Выбираем названия характеристик
     * @param array $filter
     * @param bool $count
     */
    public static function getFeatures(array $filter = [], bool $count = false)
    {

        // Фильтрация по category_id
        if (!empty($filter['category_id'])) {
            $feature_ids = ProductCategoryFeature::query()
                ->whereIn('category_id', (array) $filter['category_id'])
                ->pluck('feature_id')
                ->toArray();

            $filter['id'] = $feature_ids;
            unset($filter['category_id']);
        }

        if ($count) {
            return self::getCount();
        }

        return self::getListTranslate($filter, order: 'position')->keyBy('id');
    }


    /**
     * Количество характеристик
     */
    public static function countFeatures(array $filter = []): int
    {
        return self::getFeatures($filter, count: true);
    }


    /**
     * Выбираем характеристику по Имени
     * @param $name
     */
    public static function getFeatureByName(string $name)
    {
        return ProductFeature::where('name', trim($name))->first();
    }


    /**
     * Delete Feature
     */
    public static function deleteFeature(int $id)
    {

        // Удаляем основную характеристику
        self::where('id', $id)->delete();

        // Удаляем опции товара с этой характеристикой
        ProductOption::where('feature_id', $id)->delete();

        // Удаляем связь с категориями
        ProductCategoryFeature::where('feature_id', $id)->delete();

        // Удаляем варианты характеристик
        ProductFeatureOption::where('feature_id', $id)->delete();
    }
}
