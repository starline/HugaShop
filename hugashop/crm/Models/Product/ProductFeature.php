<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.4
 *
 */

namespace HugaShop\Models\Product;

use ReturnTypeWillChange;
use HugaShop\Models\BaseModel;
use HugaShop\Models\Localization\Language;

class ProductFeature extends BaseModel
{

    protected static $table_fields = [
        'id' =>                 ['type' => 'int',       'lenght' => 11, 'extra' => 'AUTO_INCREMENT'],
        'name' =>               ['type' => 'varchar',   'req' => true, 'trans' => true],
        'in_filter' =>          ['type' => 'tinyint',   'def' => 0],
        'position' =>           ['type' => 'int',       'def' => 0]
    ];


    public function value()
    {
        return $this->hasOne(ProductOption::class, 'feature_id');
    }


    /**
     * Выбираем названия характеристик
     * @param array $filter
     */
    public static function getFeatures(array $filter = [])
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

        // Ограничение по количеству
        if (!empty($filter['limit']) && $filter['limit'] !== 'all') {
            $query->limit((int) $filter['limit']);
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
     * Delete Feature
     */
    public static function deleteFeature(int $id)
    {
        if (empty($id)) {
            return false;
        }

        // Удаляем основную характеристику
        ProductFeature::where('id', $id)->delete();

        // Удаляем опции товара с этой характеристикой
        ProductOption::where('feature_id', $id)->delete();

        // Удаляем связь с категориями
        ProductCategoryFeature::where('feature_id', $id)->delete();

        // Удаляем варианты характеристик
        ProductFeatureVariant::where('feature_id', $id)->delete();

        return true;
    }
}
