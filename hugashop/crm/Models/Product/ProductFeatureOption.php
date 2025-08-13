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
     * Get first matching option or create a new one
     */
    public static function firstOrCreate(array $params)
    {
        $feature_id = $params['feature_id'] ?? null;
        $value = trim((string) ($params['value'] ?? ''));

        if (!$feature_id || $value === '') {
            return null;
        }

        // Ищем готовый вариант
        if ($option = self::query()
            ->where('feature_id', $feature_id)
            ->where('value', $value)
            ->first()
        ) {
            return $option;
        }

        // Генерируем уникальный URL
        $url_source = trim((string) ($params['url'] ?? $value));
        $url = self::makeUniqueUrl($url_source);

        // Создаём и возвращаем
        return self::create([
            'feature_id' => $feature_id,
            'value'      => $value,
            'url'        => $url,
        ]);
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

            // skip empty value
            $value = trim($data['value'] ?? '');
            if ($value === '') {
                continue;
            }

            $params = [
                'id'         => $data['id'] ?? null,
                'feature_id' => $feature_id,
                'value'      => $value,
                'url'        => $data['url'] ?? '',
            ];

            $option = self::firstOrCreate($params);

            if (!empty($option)) {
                $option->position = $position;
                $option->save();

                $keep_ids[] = $option->id;
            }
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
