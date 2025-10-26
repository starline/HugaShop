<?php


/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.4
 *
 */

namespace HugaShop\Models\Localization;

use HugaShop\Services\Cache;
use HugaShop\Models\BaseModel;

class Language extends BaseModel
{

    protected static $table_fields = [
        'id' =>             ['type' => 'int',      'extra' => 'AUTO_INCREMENT'],
        'code' =>           ['type' => 'varchar'],
        'country_code' =>   ['type' => 'varchar'],
        'name' =>           ['type' => 'varchar'],
        'main' =>           ['type' => 'tinyint',  'def' => 0]
    ];

    public static $languages;
    public static $main_language;
    public static $current_language;


    /**
     * Init languages from cache
     */
    private static function initLanguages()
    {
        $cache_item = Cache::getCacheItem(self::class);

        if (!$cache_item->isHit()) {
            $languages = self::query()->orderBy('id')->get();
            Cache::saveCacheItem($cache_item->set($languages));
        }

        self::$languages = $cache_item->get();
        self::$main_language = self::$languages->firstWhere('main', 1);
    }


    /**
     * Get All lenguages. Use cache
     */
    public static function getLanguages()
    {
        if (empty(self::$languages)) {
            self::initLanguages();
        }

        return self::$languages;
    }


    /**
     * Get Main Language. Use Cache
     */
    public static function getMain()
    {
        if (empty(self::$main_language)) {
            self::initLanguages();
        }

        return self::$main_language;
    }


    /**
     * Get current language
     */
    public static function getCurrent()
    {
        return self::$current_language ?: self::getMain();
    }


    /**
     * set Current language
     */
    public static function setCurrent(?string $code = null)
    {
        if (is_null($code)) {
            self::$current_language = self::getMain();
        }
        if (!empty($code)) {
            self::$current_language = self::getLanguages()->firstWhere('code', $code);
        }
        return self::$current_language;
    }


    /**
     * Check if language code exists
     */
    public static function isLanguage(string $code): bool
    {
        return (bool) self::getLanguages()->firstWhere('code', $code);
    }


    /**
     * Delete language.
     * If it's main, do not delete
     */
    public static function deleteLenguage(int $language_id)
    {
        $language = self::query()->where('id', $language_id)->first();
        if (empty($language)) {
            return false;
        }

        // If language is set as main, do not delete
        if (!empty($language->main)) {
            return false;
        }

        $result = self::deleteOne($language_id);

        Cache::deleteCacheItem(self::class); # Cache clean
        self::initLanguages();

        return $result;
    }


    /**
     * Create language.
     * If it's marked as main, remove main flag from others
     */
    public static function createOne(array|object $values): object
    {

        $values = is_array($values) ? (object) $values : $values;

        // Reset main flag for other languages
        if (!empty($values->main)) {
            self::query()->where('main', 1)->update(['main' => 0]);
        }

        $language = parent::createOne($values);

        Cache::deleteCacheItem(self::class); # Cache clean
        self::initLanguages();

        return $language;
    }


    /**
     * Update language.
     * If it's marked as main, remove main flag from others
     */
    public static function updateOne(int|array $ids, array|object $values)
    {
        $values = is_array($values) ? (object) $values : $values;

        if (!empty($values->main)) {
            $ids_array = is_array($ids) ? (array) ($ids['id'] ?? $ids) : [$ids];
            self::query()
                ->where('main', 1)
                ->whereNotIn('id', $ids_array)
                ->update(['main' => 0]);
        }

        $result = parent::updateOne($ids, $values);

        Cache::deleteCacheItem(self::class); # Cache clean
        self::initLanguages();

        return $result;
    }


    /**
     * Check if language is defined
     */
    public static function checkOrGetCode()
    {
        if (
            !empty(self::$current_language) &&
            !empty(self::$main_language) &&
            self::$current_language->code !== self::$main_language->code
        ) {
            return self::$current_language->code;
        }

        return false;
    }
}
