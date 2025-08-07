<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 3.0
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
        'index'         => ['type' => 'tinyint',    'def' => 0],
        'position'      => ['type' => 'int',        'def' => 0]
    ];

    public function options()
    {
        return $this->hasMany(ProductFeatureOption::class, 'feature_id');
    }

    /**
     * Define relation with ProductCategoryFeature
     */
    public function categories()
    {
        return $this->hasMany(ProductCategoryFeature::class, 'feature_id');
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
            return self::getCount($filter);
        }

        return self::getListTranslate($filter, order: 'position')->keyBy('id');
    }


    /**
     * Количество характеристик
     */
    public static function countFeatures(array $filter = []): int
    {
        return (int) self::getFeatures($filter, count: true);
    }


    /**
     * Получить характеристики category
     * @param array $filter
     */
    public static function getCategoryFeatures(int|array $category_id, bool $in_filter = false)
    {
        $query = self::query();

        if ($in_filter) {
            $query->where('in_filter', 1);
        }

        // Фильтрация по category_id
        $query->whereHas('categories', function ($sq) use ($category_id) {
            $sq->whereIn('category_id', (array) $category_id);
        });

        return $query->get()
            ->keyBy('id');
    }


    /**
     * Выбираем характеристику по Имени
     * @param $name
     */
    public static function getFeatureByName(string $name)
    {
        return self::where('name', trim($name))->first();
    }


    /**
     * Delete Feature
     */
    public static function deleteFeature(int $id)
    {
        // Идентификаторы вариантов характеристик
        $option_ids = ProductFeatureOption::where('feature_id', $id)
            ->pluck('id')
            ->toArray();

        // Удаляем опции товара с этой характеристикой
        ProductOption::deleteBy('feature_id', $id);

        // Удаляем связь с категориями
        ProductCategoryFeature::deleteBy('feature_id', $id);

        // Удаляем варианты характеристик
        if (!empty($option_ids)) {
            ProductFeatureOption::deleteOne($option_ids);
        }

        // Удаляем основную характеристику
        self::deleteOne($id);
    }
}
