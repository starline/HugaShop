<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.3
 *
 */

namespace HugaShop\Models\Product;

use HugaShop\Services\Helper;
use HugaShop\Models\BaseModel;
use HugaShop\Models\Localization\Language;

class ProductOption extends BaseModel
{

    protected static $table_fields = [
        'product_id' =>         ['type' => 'int',           'req' => true],
        'feature_id' =>         ['type' => 'int',           'req' => true],
        'value ' =>             ['type' => 'varchar']
    ];


    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function feature()
    {
        return $this->belongsTo(ProductFeature::class, 'feature_id');
    }

    /**
     * Select pruduct features with option value
     * @param int|array $product_id
     */
    public static function getProductOptions(int|array $product_id)
    {
        $ids = (array) $product_id;

        return self::with(['feature' => function ($query) {
            $query->orderBy('position');
        }])
            ->whereIn('product_id', $ids)
            ->get()->map(function ($option) {
                return (object)[
                    'feature_id' => $option->feature->id ?? null,
                    'name'       => $option->feature->name ?? null,
                    'value'      => $option->value,
                    'product_id' => $option->product_id,
                ];
            });
    }


    /**
     * Update option
     * @param int $product_id
     * @param int $feature_id
     */
    public static function updateOption(int $product_id, int $feature_id, $value)
    {
        if (!empty($value)) {

            // Используем updateOrCreate как аналог REPLACE INTO
            self::updateOrCreate(
                [
                    'product_id' => $product_id,
                    'feature_id' => $feature_id
                ],
                [
                    'value' => $value
                ]
            );
        } else {

            // Удаляем запись, если значение пустое
            self::where('product_id', $product_id)
                ->where('feature_id', $feature_id)
                ->delete();
        }

        return true;
    }


    public static function deleteOption($product_id, $feature_id)
    {
        return self::where('product_id', $product_id)
            ->where('feature_id', $feature_id)
            ->limit(1)
            ->delete() > 0;
    }


    /**
     * Выбираем варианты характеристик
     * @param array $filter
     */
    public static function getOptions(array $filter = [])
    {

        if (!Helper::checkFilterParams($filter, ['feature_id', 'product_id', 'category_id', 'brand_id'])) {
            return array();
        }

        $query = self::query()
            ->with(['product', 'feature']); // eager load отношения

        // Присоединение таблицы продуктов для фильтрации по видимости и категории
        if (isset($filter['visible'])) {
            $query->whereHas('product', function ($q) use ($filter) {
                $q->where('visible', (int) $filter['visible']);

                if (isset($filter['category_id'])) {
                    $q->whereIn('category_id', (array)$filter['category_id']);
                }
            });
        }

        if (isset($filter['feature_id'])) {
            $query->whereIn('feature_id', (array)$filter['feature_id']);
        }

        if (isset($filter['product_id'])) {
            $query->whereIn('product_id', (array)$filter['product_id']);
        }

        if (isset($filter['brand_id'])) {
            $query->whereHas('product', function ($q) use ($filter) {
                $q->whereIn('brand_id', (array)$filter['brand_id']);
            });
        }

        if (!empty($filter['keyword'])) {
            $query->where('value', 'like', '%' . $filter['keyword'] . '%');
        }

        if (!empty($filter['features'])) {
            foreach ($filter['features'] as $feature => $value) {
                $query->where(function ($q) use ($feature, $value) {
                    $q->where('feature_id', $feature)
                        ->orWhereHas('product.options', function ($sub) use ($feature, $value) {
                            $sub->where('feature_id', $feature)
                                ->where('value', $value);
                        });
                });
            }
        }

        if (isset($filter['limit']) && $filter['limit'] !== 'all') {
            $query->limit((int) $filter['limit']);
        }

        $query->orderByRaw('value = 0, -value DESC, value');

        return $query->get();
    }


    /**
     * Get All feature variants
     */
    public static function getAllVariants(array $filter)
    {
        $query = self::query()->select('value');

        if (isset($filter['feature_id'])) {
            $query->where('feature_id', $filter['feature_id']);
        }

        if (!empty($filter['keyword'])) {
            $query->where('value', 'like', '%' . $filter['keyword'] . '%');
        }

        if (isset($filter['limit']) && $filter['limit'] !== 'all') {
            $query->limit((int) $filter['limit']);
        }

        $query->groupBy('value')
            ->orderByRaw('value = 0, -value DESC, value');

        $feature_variants =  $query->get();

        // Fill translations
        if ($code = Language::checkOrGetCode() and self::isTranslatable()) {
            self::fillTranslations($feature_variants, $code, merge_fields: true);
        }

        return $feature_variants;
    }
}
