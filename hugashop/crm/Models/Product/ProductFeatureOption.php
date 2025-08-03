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
use HugaShop\Models\Localization\Language;
use HugaShop\Models\Product\ProductFeature;

class ProductFeatureOption extends BaseModel
{

    public $timestamps = true;
    protected static $table_fields = [
        'id' =>             ['type' => 'int',      'extra' => 'AUTO_INCREMENT'],
        'feature_id' =>     ['type' => 'int',      'req' => true],
        'value' =>          ['type' => 'varchar',  'req' => true,   'trans' => true,    'search' => true]
    ];


    public function feature()
    {
        return $this->belongsTo(ProductFeature::class, 'feature_id');
    }


    /**
     * Обновление вариантов характеристик
     * @param int $feature_id
     * @param array $options
     */
    public static function updateFeatureOptions(int $feature_id, array $options)
    {
        if (empty($feature_id)) {
            return false;
        }

        // Prepare incoming values
        $values = array_unique(
            array_filter(
                array_map('trim', $options),
                fn($v) => $v !== ''
            )
        );

        $language_code = Language::checkOrGetCode();

        // If editing translation only update translation records
        if ($language_code) {
            $ids = self::where('feature_id', $feature_id)
                ->pluck('id')
                ->toArray();

            foreach ($ids as $id) {
                if (!empty($values[$id])) {
                    self::updateOrCreateTranslation($id, $language_code, ['value' => $values[$id]]);
                }
            }

            return true;
        }

        $keep_ids = [];

        foreach ($values as $id => $value) {

            $option = self::find($id);

            if ($option) {
                $option->value = $value;
                $option->save();
            } else {

                // Если ID не найден — создаём новую без id
                $option = self::firstOrCreate([
                    'feature_id' => $feature_id,
                    'value'      => $value,
                ]);
            }

            $keep_ids[] = $option->id;
        }


        self::where('feature_id', $feature_id)
            ->whereNotIn('id', $keep_ids)
            ->delete();

        return true;
    }
}
