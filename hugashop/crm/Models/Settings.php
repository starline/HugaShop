<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.9
 *
 * Управление настройками магазина, хранящимися в базе данных
 * В отличие от класса Config оперирует настройками доступными админу и хранящимися в базе данных.
 *
 */

namespace HugaShop\Models;

use HugaShop\Services\Cache;
use HugaShop\Services\Helper;

class Settings extends BaseModel
{

    protected $guarded = ['id'];

    protected static $table_fields = [
        'id' =>                 ['type' => 'int',           'extra' => 'AUTO_INCREMENT'],
        'name' =>               ['type' => 'varchar'],
        'value' =>              ['type' => 'varchar',       'length' => 900]
    ];

    private static $vars = [];


    /**
     * Get all paramd and caching
     */
    private static function getInstance()
    {
        if (empty(self::$vars)) {

            // The callable will only be executed on a cache miss.
            $settings = Cache::cache(self::class)->get('settings', function (): array {

                // Select settings from DB
                $settings_vars = [];
                foreach (self::query()->select('name', 'value')->get() as $setting) {
                    if (!($settings_vars[$setting->name] = @unserialize($setting->value))) {
                        $settings_vars[$setting->name] = $setting->value;
                    }
                }

                return $settings_vars;
            });

            self::$vars = $settings;
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
            return (object) self::$vars;
        }

        // Для определения Settings vars
        if (isset(self::$vars[$param_name])) {
            return self::$vars[$param_name];
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

        Cache::cache(self::class)->clear(); # Cache clean

        self::$vars[$name] = $value;

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
