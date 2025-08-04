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
        $product_options = self::getList(['product_id' => $product_id]);

        $features_ids = $product_options->pluck('feature_id')->toArray();
        $options_ids  = $product_options->pluck('option_id')->toArray();

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
            ->selectRaw('feature_id, option_id, COUNT(DISTINCT product_id) AS product_count')
            ->groupBy('feature_id', 'option_id');

        // Фильтр по ID характеристик
        if (!empty($filter['feature_id'])) {
            $query->whereIn('feature_id', (array) $filter['feature_id']);
        }

        // Фильтр по видимости товара
        if (!empty($filter['product_visible'])) {
            $query->whereHas('product', function ($q) use ($filter) {
                $q->where('visible', (int) $filter['product_visible']);
            });
        }

        // Фильтр по категориям
        if (!empty($filter['category_id'])) {
            $query->whereHas('product', function ($q) use ($filter) {
                $q->whereIn('category_id', (array) $filter['category_id']);
            });
        }

        // Показывать только характеристики, доступные в фильтре
        if (!empty($filter['feature_in_filter'])) {
            $query->whereHas('feature', function ($q) use ($filter) {
                $q->where('in_filter', (int) $filter['feature_in_filter']);
            });
        }

        // Фильтр по выбранным характеристикам товара
        if (!empty($filter['feature_selected']) && is_array($filter['feature_selected'])) {
            $query->where(function ($query) use ($filter) {
                foreach ($filter['feature_selected'] as $feature_id => $option_ids) {
                    $query->where(function ($sub) use ($feature_id, $option_ids) {
                        $sub->where('feature_id', $feature_id)
                            ->orWhereIn('product_id', function ($q) use ($option_ids) {

                                // находим товары, у которых уже выбраны переданные опции
                                $q->select('product_id')
                                    ->from((new self())->getTable())
                                    ->whereIn('option_id', (array) $option_ids);
                            });
                    });
                }
            });
        }

        $pairs = $query->get();
        if ($pairs->isEmpty()) {
            return collect();
        }

        $product_counts = $pairs->mapWithKeys(function ($item) {
            return [$item->option_id => $item->product_count];
        });

        $feature_ids = $pairs->pluck('feature_id')->unique()->values()->all();
        $option_ids  = $pairs->pluck('option_id')->unique()->values()->all();

        $features = ProductFeature::getListTranslate(['id' => $feature_ids], 'position')->keyBy('id');
        $options  = ProductFeatureOption::getListTranslate(['id' => $option_ids])->groupBy('feature_id');

        $result = collect();
        foreach ($features as $feature) {
            if (isset($options[$feature->id]) && $options[$feature->id]->isNotEmpty()) {
                $feature_temp = $feature;

                foreach ($options[$feature->id] as $option) {
                    $option->product_count = $product_counts[$option->id] ?? 0;
                }

                $feature_temp->options = $options[$feature->id] ?? collect();
                $result->push($feature_temp);
            }
        }

        return $result;
    }
}
