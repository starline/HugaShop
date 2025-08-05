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

            $value = trim($data['value'] ?? '');
            if ($value === '') {
                continue;
            }

            $id     = $data['id'] ?? null;
            $url    = $data['url'] ? self::makeUniqueUrl($feature_id, $data['url'], $id) : '';
            $option = $id ? self::find($id) : null;

            if ($option) {
                $option->value    = $value;
                $option->url      = $url !== '' ? $url : $option->id;
                $option->position = $position;
                $option->save();
            } else {
                $option = self::create([
                    'feature_id' => $feature_id,
                    'value'      => $value,
                    'url'        => $url,
                    'position'   => $position,
                ]);

                if ($option->url === '') {
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


    /**
     * Генерация уникального url для характеристики
     * @param int $feature_id
     * @param string $url
     * @param int $except_id
     */
    protected static function makeUniqueUrl(int $feature_id, string $url, ?int $except_id = null): string
    {
        if ($url === '' || !$except_id) {
            return $url;
        }

        $base = Helper::slugEn($url);
        $i    = 1;

        while (self::where('feature_id', $feature_id)
            ->where('url', $url)
            ->where('id', '!=', $except_id)
            ->exists()
        ) {
            $i++;
            $url = $base . '-' . $i;
        }

        return $url;
    }
}
