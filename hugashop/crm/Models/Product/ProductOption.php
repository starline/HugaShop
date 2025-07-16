<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.4
 *
 */

namespace HugaShop\Models\Product;

use HugaShop\Services\Helper;
use HugaShop\Models\BaseModel;

class ProductOption extends BaseModel
{

    protected static $table_fields = [
        'id' =>                ['type' => 'int',       'extra' => 'AUTO_INCREMENT'],
        'product_id' =>        ['type' => 'int',       'req' => true],
        'feature_id' =>        ['type' => 'int',       'req' => true],
        'option_id' =>         ['type' => 'int',       'req' => true]
    ];


    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function feature()
    {
        return $this->belongsTo(ProductFeature::class, 'feature_id');
    }

    public function option()
    {
        return $this->belongsTo(ProductFeatureOption::class, 'option_id');
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
            ->with('option')
            ->whereIn('product_id', $ids)
            ->get()->map(function ($option) {
                return (object)[
                    'feature_id' => $option->feature->id,
                    'name'       => $option->feature->name,
                    'option_id'  => $option->option->id,
                    'value'      => $option->option->value,
                    'product_id' => $option->product_id,
                ];
            });
    }


    /**
     * Update option
     * @param int $product_id
     * @param int $feature_id
     */
    public static function updateOption(int $product_id, int $feature_id, string $value)
    {
        $value = trim((string) $value);

        // Получаем вариант характеристики или создаём новый
        $featureOption = ProductFeatureOption::firstOrCreate([
            'feature_id' => $feature_id,
            'value'      => $value,
        ]);

        // Сохраняем связь товара и характеристики
        return self::updateOrCreate(
            [
                'product_id' => $product_id,
                'feature_id' => $feature_id,
            ],
            [
                'option_id' => $featureOption->id,
            ]
        );
    }


    /**
     * Delete option
     */
    public static function deleteOption($product_id, $feature_id)
    {
        return self::where('product_id', $product_id)
            ->where('feature_id', $feature_id)
            ->delete() > 0;
    }


    /**
     * Выбираем варианты характеристик
     * @param array $filter
     */
    public static function getOptions(array $filter = [])
    {

        if (!Helper::checkFilterParams($filter, ['feature_id', 'product_id', 'category_id'])) {
            return array();
        }

        $query = self::query()
            ->with(['product', 'feature', 'option']); // eager load отношения

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
}
