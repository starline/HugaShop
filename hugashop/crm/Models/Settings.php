<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 3.0
 *
 * Управление настройками магазина, хранящимися в базе данных
 * В отличие от класса Config оперирует настройками доступными админу и хранящимися в базе данных.
 *
 */

namespace HugaShop\Models;

use HugaShop\Services\Cache;

class Settings extends BaseModel
{

    protected $guarded = ['id'];

    protected static $table_fields = [
        'id' =>                 ['type' => 'int',           'extra' => 'AUTO_INCREMENT'],
        'name' =>               ['type' => 'varchar'],
        'value' =>              ['type' => 'varchar',       'length' => 900]
    ];

    private static $settings = [];


    /**
     * Get all paramd and caching
     */
    private static function getInstance()
    {
        if (empty(self::$settings)) {

            $cache_item = Cache::getCacheItem(self::class);

            if (!$cache_item->isHit()) {

                // Select settings from DB
                $settings_vars = [];
                foreach (self::query()->select('name', 'value')->get() as $setting) {
                    if (!($settings_vars[$setting->name] = @unserialize($setting->value))) {
                        $settings_vars[$setting->name] = $setting->value;
                    }
                }

                Cache::saveCacheItem($cache_item->set($settings_vars));
            }

            self::$settings = $cache_item->get();
        }
    }


    /**
     * Выбираем переменную
     * @param string $param_name
     */
    public static function getParam(?string $param_name = null)
    {
        self::getInstance();

        if (is_null($param_name)) {
            return (object) self::$settings;
        }

        // Для определения Settings vars
        if (isset(self::$settings[$param_name])) {
            return self::$settings[$param_name];
        } else {
            return null;
        }
    }


    /**
     * Get All Settings
     */
    public static function getAllParams()
    {
        return self::getParam();
    }


    /**
     * Set variable
     * @param string $name
     * @param mixed $value
     */
    public static function set(string $name, mixed $value)
    {
        self::getInstance();
        Cache::deleteCacheItem(self::class); # Cache clean
        self::$settings[$name] = $value;

        if (is_array($value)) {
            $value = serialize($value);
        }

        return self::query()->updateOrCreate(['name' => $name], ['name' => $name, 'value' => $value]);
    }


    /**
     * Get all settings
     */
    public static function getAll()
    {
        return self::getParam();
    }
}
