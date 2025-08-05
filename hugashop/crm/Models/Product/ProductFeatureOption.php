<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.6
 *
 */

namespace HugaShop\Models\Product;

use HugaShop\Services\Helper;
use HugaShop\Models\BaseModel;
use HugaShop\Models\Localization\Language;
use HugaShop\Models\Product\ProductFeature;

class ProductFeatureOption extends BaseModel
{

    public $timestamps = true;
    protected static $table_fields = [
        'id'            => ['type' => 'int',      'extra' => 'AUTO_INCREMENT'],
        'feature_id'    => ['type' => 'int',      'req' => true],
        'url'           => ['type' => 'varchar'],
        'value'         => ['type' => 'varchar',  'req' => true,   'trans' => true,    'search' => true],
        'position'      => ['type' => 'int',      'def' => 0]
    ];


    public function feature()
    {
        return $this->belongsTo(ProductFeature::class, 'feature_id');
    }


    /**
     * Обновление вариантов характеристик
     * @param int $feature_id
     * @param array $options [ [id, value, url], ... ]
     */
    public static function updateFeatureOptions(int $feature_id, array $options)
    {
        if (empty($feature_id)) {
            return false;
        }

        // Обновление только переводов
        if ($language_code = Language::checkOrGetCode()) {
            $options_trans = array_filter($options, function ($option) {
                return isset($option['id'], $option['value']) && trim($option['value']) !== '';
            });

            foreach ($options_trans as $option) {
                self::updateOrCreateTranslation((int) $option['id'], $language_code, ['value' => trim($option['value'])]);
            }

            return true;
        }


        $keep_ids = [];
        foreach ($options as $position => $data) {
            $id    = (int) ($data['id'] ?? 0);
            $value = trim($data['value'] ?? '');
            $url   = $data['url'] ? Helper::slugEn($data['url']) : '';

            if ($value === '') {
                continue;
            }

            $option = null;

            if ($id > 0) {
                $option = self::find($id);
            }

            if ($option) {
                $option->value    = $value;
                $option->url      = ($url !== '' && (!is_numeric($url) || (is_numeric($url) && $url === $option->id))) ? $url : $option->id;
                $option->position = $position;
                $option->save();
            } else {
                $option = self::create([
                    'feature_id' => $feature_id,
                    'value'      => $value,
                    'url'        => $url,
                    'position'   => $position,
                ]);

                if ($option->url === '' || (is_numeric($option->url) && $option->url !== $option->id)) {
                    $option->url = $option->id;
                    $option->save();
                }
            }

            $keep_ids[] = $option->id;
        }

        self::where('feature_id', $feature_id)
            ->whereNotIn('id', $keep_ids)
            ->delete();

        return true;
    }
}
