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
     * @param int $product_id
     */
    public static function getProductOptions(int $product_id)
    {
        $product_options = ProductOption::getList(['product_id' => $product_id]);

        $features_ids   = $product_options->pluck('feature_id')->toArray();
        $options_ids    = $product_options->pluck('option_id')->toArray();

        $features   = ProductFeature::getListTranslate(['id' => $features_ids], 'position');
        $options    = ProductFeatureOption::getListTranslate(['id' => $options_ids])->keyBy('feature_id');

        foreach ($features as $feature) {
            $feature->value     = $options[$feature->id]->value;
            $feature->option_id = $options[$feature->id]->id;
        }

        return $features;
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
    public static function deleteProductOption($product_id)
    {
        return self::where('product_id', $product_id)->delete();
    }


    /**
     * Выбираем варианты характеристик
     * @param array $filter
     */
    public static function getOptions(array $filter = [])
    {

        $query = self::query()
            ->with(['product', 'feature', 'option']);

        // Присоединение таблицы продуктов для фильтрации по видимости и категории
        if (isset($filter['product_visible'])) {
            $query->whereHas('product', function ($sub_query) use ($filter) {
                $sub_query->where('visible', (int) $filter['product_visible']);

                if (isset($filter['category_id'])) {
                    $sub_query->whereIn('category_id', (array)$filter['category_id']);
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
            foreach ($filter['features'] as $feature_id => $value) {
                $query->where(function ($sub_query) use ($feature_id, $value) {
                    $sub_query->where('feature_id', $feature_id)
                        ->orWhereHas('product.options', function ($sub) use ($feature_id, $value) {
                            $sub->where('feature_id', $feature_id)
                                ->where('value', $value);
                        });
                });
            }
        }

        //$query->orderByRaw("option.value = 0, -option.value DESC, option.value");

        return $query->get();
    }
}
