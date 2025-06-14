<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.3
 *
 */

namespace HugaShop\Api;

use HugaShop\Api\Settings;

class Extension
{

    private static $places = [
        'front_head',
        'front_body',
        'admin_order_side'
    ];

    private static $extension_pool = [];

    /**
     * Update
     * @param string $name
     * @param array $settings
     */
    public static function updateExt(string $name, array $settings = [])
    {
        // Cache clean
        Helper::cache()->delete(Helper::class_basename(self::class)); # clean cache
        $settings = serialize(empty($settings) ? [] : $settings);
        return Settings::set($name, $settings);
    }


    /**
     * Get Modules
     * @param array $filter
     */
    public static function getExtensionsList()
    {
        return Helper::getModules(Config::get('extension_dir'));
    }


    /**
     * Make Extension
     * @param string $name
     */
    public static function makeExtension(string $name)
    {

        // Get from Pool
        if (isset(self::$extension_pool[$name])) {
            return self::$extension_pool[$name];
        }

        // Make new one
        $ClassName = "HugaShop\\Extensions\\{$name}\\{$name}";
        if (class_exists($ClassName)) {
            return self::$extension_pool[$name] = new $ClassName();;
        }

        return null;
    }


    /**
     * Get Extensions by place
     * Use Cache
     * @param string $places
     */
    public static function getExtensionsByPlace(string $place)
    {

        // Get Places Extensions
        // Example: array('front_head' => ['ExtensionFirst', 'ExtensionSecond'], 'front_body' => ['ExtensionThird'])
        // Cache
        $cache_item = Helper::cache()->getItem(Helper::class_basename(self::class));
        if (!$cache_item->isHit()) {

            $places = [];

            $ext_list = self::getExtensionsList();
            foreach ($ext_list as $ext) {
                if (!empty($Ext = self::makeExtension($ext->module))) {
                    foreach (self::$places as $place_name) {
                        if (method_exists($Ext, 'get' . ucfirst(Helper::snakeToCamelCase($place_name)) . 'Template')) { # Example: getFrontHeadTemplate
                            if (!empty($Ext->ext_settings->enabled)) {
                                $places[$place_name][]  = $ext->module;
                            }
                        }
                    }
                }
            }

            Helper::cache()->save($cache_item->set($places));
        }

        $places = $cache_item->get();

        if (!empty($places[$place])) {
            return $places[$place];
        }

        return [];
    }
}
