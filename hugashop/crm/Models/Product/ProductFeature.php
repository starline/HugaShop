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
use HugaShop\Models\Localization\Language;
use HugaShop\Models\Product\ProductFeatureOption;

class ProductFeature extends BaseModel
{

    public $timestamps = true;

    protected static $table_fields = [
        'id' =>                 ['type' => 'int',       'extra' => 'AUTO_INCREMENT'],
        'name' =>               ['type' => 'varchar',   'req' => true,  'trans' => true,    'search' => true],
        'in_filter' =>          ['type' => 'tinyint',   'def' => 0],
        'position' =>           ['type' => 'int',       'def' => 0]
    ];


    /**
     * Выбираем названия характеристик
     * @param array $filter
     * @param bool $count
     */
    public static function getFeatures(array $filter = [], bool $count = false)
    {

        $query = ProductFeature::query();

        // Фильтрация по category_id
        if (!empty($filter['category_id'])) {
            $query->whereIn('id', function ($subquery) use ($filter) {
                $subquery->select('feature_id')
                    ->from('product_category_feature')
                    ->whereIn('category_id', (array) $filter['category_id']);
            });
        }

        // Фильтрация по in_filter
        if (isset($filter['in_filter'])) {
            $query->where('in_filter', intval($filter['in_filter']));
        }

        // Фильтрация по id
        if (!empty($filter['id'])) {
            $query->whereIn('id', (array) $filter['id']);
        }

        // Поиск по ключевому слову
        if (!empty($filter['keyword'])) {
            $query->where('name', 'like', '%' . $filter['keyword'] . '%');
        }

        // Сортировка
        $query->orderBy('position');

        if ($count) {
            return $query->count();
        }

        // Pagination
        if (!empty($filter['limit']) && $filter['limit'] !== 'all') {
            $limit = max(1, (int)$filter['limit']);
            $page = max(1, (int)($filter['page'] ?? 1));
            $query->limit($limit)->offset(($page - 1) * $limit);
        }

        $features = $query->get()->keyBy('id');

        // Fill translations
        if ($code = Language::checkOrGetCode() and self::isTranslatable()) {
            self::fillTranslations($features, $code, merge_fields: true);
        }

        return $features;
    }


    /**
     * Выбираем название характеристики
     * @param $id
     */
    public static function getFeature(int|string $id)
    {
        if (is_numeric($id)) {
            return ProductFeature::find($id);
        }

        return ProductFeature::where('name', trim($id))->first();
    }


    /**
     * Количество характеристик
     */
    public static function countFeatures(array $filter = []): int
    {
        return self::getFeatures($filter, true);
    }


    /**
     * Delete Feature
     */
    public static function deleteFeature(int $feature_id)
    {

        // Удаляем основную характеристику
        self::where('id', $feature_id)->delete();

        // Удаляем опции товара с этой характеристикой
        ProductOption::where('feature_id', $feature_id)->delete();

        // Удаляем связь с категориями
        ProductCategoryFeature::where('feature_id', $feature_id)->delete();

        // Удаляем варианты характеристик
        ProductFeatureOption::where('feature_id', $feature_id)->delete();
    }
}
