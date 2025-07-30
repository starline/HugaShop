<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.7
 *
 */

namespace HugaShop\Extensions;

use HugaShop\Models\Settings;
use HugaShop\Services\Config;
use HugaShop\Services\Design;
use HugaShop\Services\Helper;

class BaseExtension
{

    /**
     * Get Extension name (Module)
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
     * Get extension params
     */
    public static function getExtension()
    {
        $extension = self::getConfig();
        $extension->settings = self::getSettings();
        $extension->hasIndex = self::hasIndex();
        return $extension;
    }


    /**
     * Has index function
     */
    public static function hasIndex()
    {
        return method_exists(static::class, 'index') ? true : false;
    }


    /**
     * Check if extension is enabled
     * @return bool
     */
    public static function isEnabled(): bool
    {
        return static::getSettings('enabled') ? true : false;
    }


    /**
     * Get Extension directory
     */
    public static function getExtensionDir()
    {
        return Config::get('extension_dir') . self::getName() . '/';
    }


    /**
     * Get Extension config
     */
    public static function getConfig(?string $param = null)
    {
        $config = Helper::getModule(self::getName(), Config::get('extension_dir'));

        if (is_null($param)) {
            return $config;
        }
        return $config->$param ?? null;
    }


    /**
     * Fetch extension template 
     * @param string $template
     */
    public static function fetchTemplate(string $template)
    {
        return Design::fetch(self::getTemplatePath($template));
    }


    /**
     * Get extension template
     * @param string $template
     */
    public static function getTemplatePath(string $template)
    {
        return self::getExtensionDir() . 'templates/' . $template;
    }


    /**
     * Ajax. Update Model. Clear cache
     * Make: HugaShop\Extensions\InfoBlock\Models\InfoBlock
     * From: HugaShop\Extensions\InfoBlock\InfoBlock
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
