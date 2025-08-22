<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.9
 *
 */

namespace HugaShop\Addons;

use HugaShop\Models\Settings;
use HugaShop\Services\Config;
use HugaShop\Services\Design;
use HugaShop\Services\Helper;

class BaseAddon
{

    /**
     * Get Addon name (Module)
     */
    public static function getName()
    {
        return class_basename(static::class);
    }


    /**
     * Get setting param
     */
    public static function getSettings(?string $param = null)
    {
        $settings = (object) (Settings::getParam(self::getName()) ?? []); # was array

        if (is_null($param)) {
            return $settings;
        }
        return $settings->$param ?? null;
    }


    /**
     * Get addon params
     */
    public static function getAddon()
    {
        $addon = self::getConfig();
        $addon->settings = self::getSettings();
        return $addon;
    }


    /**
     * Check if addon is enabled
     * @return bool
     */
    public static function isEnabled(): bool
    {
        return static::getSettings('enabled') ? true : false;
    }


    /**
     * Get Addon directory
     */
    public static function getAddonDir()
    {
        return Config::get('addon_dir') . self::getName() . '/';
    }


    /**
     * Get Addon config
     */
    public static function getConfig(?string $param = null)
    {
        $config = Helper::getModule(self::getName(), Config::get('addon_dir'));

        if (is_null($param)) {
            return $config;
        }
        return $config->$param ?? null;
    }


    /**
     * Fetch addon template 
     * @param string $template
     */
    public static function fetchTemplate(string $template)
    {
        return Design::fetch(self::getTemplatePath($template));
    }


    /**
     * Get addon template
     * @param string $template
     */
    public static function getTemplatePath(string $template)
    {
        return self::getAddonDir() . 'templates/' . $template;
    }


    /**
     * Ajax. Update Model. Clear cache
     * Make: HugaShop\Addons\InfoBlock\Models\InfoBlock
     * From: HugaShop\Addons\InfoBlock\InfoBlock
     */
    public static function updateOne(int $id, $entity)
    {
        // Main Model is always ClassName . Model
        $base_namespace     = preg_replace('/\\\\' . preg_quote(self::getName(), '/') . '$/', '', static::class);
        $class              = $base_namespace . '\\Models\\' . self::getName();

        $class::updateOne($id, $entity);
        $class::cacheClear();
    }
}
