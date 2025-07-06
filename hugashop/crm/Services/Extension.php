<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.4
 *
 */

namespace HugaShop\Services;

use HugaShop\Models\Settings;
use HugaShop\Services\Config;
use HugaShop\Services\Helper;

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
        Helper::cache()->delete($name); # clean cache
        $settings = empty($settings) ? [] : $settings;
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

        $places = [];

        $ext_list = self::getExtensionsList();
        foreach ($ext_list as $ext) {
            if (!empty($Ext = self::makeExtension($ext->module))) {
                foreach (self::$places as $place_name) {
                    if (method_exists($Ext, 'get' . ucfirst(Helper::snakeToCamelCase($place_name)) . 'Template')) { # Example: getFrontHeadTemplate
                        if (!empty($Ext->settings->enabled)) {
                            $places[$place_name][]  = $ext->module;
                        }
                    }
                }
            }
        }

        if (!empty($places[$place])) {
            return $places[$place];
        }

        return [];
    }
}
