<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.8
 *
 */

namespace HugaShop\Services;

use HugaShop\Services\Cache;
use HugaShop\Models\Settings;
use HugaShop\Services\Config;
use HugaShop\Services\Helper;

class Addon
{

    private static $places = [
        'front_head',
        'front_body',
        'admin_order_side'
    ];

    private static $place_cache = [];

    /**
     * Available admin menu sections
     * @var array<int, string>
     */
    private static $menu_sections = [
        'crm',
        'warehouse',
        'clients',
        'content',
        'finance',
        'addon',
        'settings'
    ];

    /**
     * Update
     * @param string $name
     * @param array $settings
     */
    public static function updateExt(string $name, array $settings = [])
    {
        // Cache clean
        Cache::mainCache()->delete($name); # clean cache
        Cache::cache(self::class)->clear();
        $settings = empty($settings) ? [] : $settings;
        return Settings::set($name, $settings);
    }


    /**
     * Get Modules
     * @param array $filter
     */
    public static function getAddonsList()
    {
        return Helper::getModules(Config::get('addon_dir'));
    }


    /**
     * Get addons list for Admin menu
     */
    public static function getMenuAddons(?string $section = null)
    {
        $menu_addons = [];
        foreach (self::getAddonsList() as $ext) {
            if (empty($Ext = self::getNameSpace($ext->module))) {
                continue;
            }
            $settings = $Ext::getSettings();
            if (empty($settings->show_menu)) {
                continue;
            }
            $place = $Ext::getConfig('menu_section') ?? 'addon';
            if (!in_array($place, self::$menu_sections, true)) {
                $place = 'addon';
            }
            if ($section === null) {
                $menu_addons[$place][] = $ext;
            } elseif ($section === $place) {
                $menu_addons[] = $ext;
            }
        }
        return $menu_addons;
    }


    public static function getNameSpace(string $name)
    {
        $name_space = "HugaShop\\Addons\\{$name}\\{$name}";
        return class_exists($name_space) ? $name_space : null;
    }


    /**
     * Get Addons name list by place
     * Use Cache
     * @param string $place front_head | front_body
     */
    public static function getAddonsByPlace(string $place)
    {

        if (isset(self::$place_cache[$place])) {
            return self::$place_cache[$place];
        }

        $cache_item = Cache::cache(self::class)->getItem('place_' . $place);
        if (!$cache_item->isHit()) {
            $places = [];
            $ext_list = self::getAddonsList();
            foreach ($ext_list as $ext) {
                if (!empty($Ext = self::getNameSpace($ext->module))) {
                    foreach (self::$places as $place_name) {
                        $get_method = 'get' . ucfirst(Helper::snakeToCamelCase($place_name)) . 'Template';
                        if (method_exists($Ext, $get_method)) {
                            if (!empty($Ext::getSettings()->enabled)) {
                                $places[$place_name][] = $ext->module;
                            }
                        }
                    }
                }
            }

            $cache_value = $places[$place] ?? [];
            Cache::cache(self::class)->save($cache_item->set($cache_value));
        }

        // List of addons in current place
        return self::$place_cache[$place] = $cache_item->get();
    }
}
