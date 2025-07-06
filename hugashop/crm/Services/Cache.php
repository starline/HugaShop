<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.1
 *
 */

namespace HugaShop\Services;

use HugaShop\Models\Localization\Language;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;


class Cache
{

    private static $cache;

    /**
     * Cache. Language dependency
     * @param ?string $name = null default HugaShop
     * @param int $time Time in seconds. Default 0
     */
    public static function cache(?string $name = null, int $time = 0, $lang = false)
    {
        $name = is_null($name) ? 'HugaShop' : str_replace('\\', '', $name);

        if ($lang === true) {
            $locale = Language::getCurrent()->code;
            $name .= '_' . $locale;
        }

        return self::$cache[$name] ?? self::$cache[$name] = new FilesystemAdapter($name, $time, Config::get('api_cache_dir'));
    }


    /**
     * Get item from mane cache
     */
    public static function getCacheItem(string $item_name, int $time = 0)
    {
        $item_name = class_basename($item_name);
        return self::cache(null, $time)->getItem($item_name);
    }


    /**
     * Save item to main chache
     */
    public static function saveCacheItem($cache_item)
    {
        self::cache()->save($cache_item);
    }


    /**
     * Delete item from main chache
     */
    public static function deleteCacheItem(string $item_name)
    {
        $item_name = class_basename($item_name);
        self::cache()->delete($item_name);
    }


    /**
     * Cache for language 
     */
    public static function cacheLang(?string $name = null, int $time = 0)
    {
        self::cache($name, $time, true);
    }
}
