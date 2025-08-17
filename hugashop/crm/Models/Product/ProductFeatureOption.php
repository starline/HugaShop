<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.7
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
        'url'           => ['type' => 'varchar',  'slug' => true],
        'value'         => ['type' => 'varchar',  'req' => true,   'trans' => true,    'search' => true],
        'position'      => ['type' => 'int',      'def' => 0]
    ];


    public function feature()
    {
        return $this->belongsTo(ProductFeature::class, 'feature_id');
    }


    /**
     * Get first matching option or create a new one
     */
    public static function firstOrCreate(array $params)
    {
        $id         = $params['id'] ?? null;
        $feature_id = $params['feature_id'] ?? null;
        $value      = trim((string) ($params['value'] ?? ''));
        $url        = $params['url'] ?? null;

        if ((!$feature_id || $value === '') && !$id) {
            return null;
        }

        unset($params['url'], $params['id']);

        if ($id && ($option = self::find($id))) {
            $option->fill($params);
        } else {
            $option = self::updateOrCreate(
                ['feature_id' => $feature_id, 'value' => $value],
                $params
            );
        }

        if (!empty($url)) {
            $option->url = self::makeUniqueUrl($url, $option->id);
        } elseif (empty($option->url)) {
            $option->url = self::makeUniqueUrl($option->id, $option->id);
        }

        $option->save();

        return $option;
    }


    /**
     * Обновление вариантов характеристик
     * @param int $feature_id
     * @param array $options [ [id, value, url], ... ]
     */
    public static function updateFeatureOptions(int $feature_id, array $options)
    {

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

            // Skip empty value
            $value = trim($data['value'] ?? '');
            if ($value === '') {
                continue;
            }

            $params = [
                'id'         => $data['id'] ?? null,
                'feature_id' => $feature_id,
                'value'      => $value,
                'url'        => $data['url'] ?? null,
                'position'   => $position
            ];

            $option = self::firstOrCreate($params);
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
     * @param ?int $except_id
     */
    protected static function makeUniqueUrl(string $url, ?int $except_id = null): string
    {

        $base = Helper::slugEn($url);
        $url = $base;

        $query = self::query();

        // exception
        if ($except_id) {
            $query->where('id', '!=', $except_id);
        }

        $i = 1;
        while ($query->clone()->where('url', $url)->exists()) {
            $url = $base . '-' . $i++;
        }

        return $url;
    }
}
