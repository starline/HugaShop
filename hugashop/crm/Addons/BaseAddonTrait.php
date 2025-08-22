<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.8
 *
 */

namespace HugaShop\Addons;

use HugaShop\Models\Settings;
use HugaShop\Services\Config;
use HugaShop\Services\Helper;

trait BaseAddonTrait
{

    /** 
     * Universal get addon name
     */
    private static function getName(?string $class = null)
    {
        // Получаем полное имя текущего класса
        $class = $class ?: static::class;

        // Приводим к нормальному виду (на всякий случай)
        $class = str_replace('/', '\\', $class);

        // Разбиваем по namespace
        $parts = explode('\\', $class);

        // Ищем "Addons" и возвращаем следующий сегмент
        $index = array_search('Addons', $parts);

        return ($index !== false && isset($parts[$index + 1]))
            ? $parts[$index + 1]
            : null;
    }


    /**
     * Get Addon settings
     */
    private static function getSettings(?string $param = null)
    {
        $addon_name = self::getName();
        $result = $settings = (object) (Settings::getParam($addon_name) ?? []);

        if (!is_null($param)) {
            $result = $settings->$param ?? null;
        }

        return $result;
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
     * Get Addon directory
     */
    public static function getAddonDir()
    {
        return Config::get('addon_dir') . self::getName() . '/';
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
     * Get Addon
     */
    public function getAddon()
    {
        $addon = self::getConfig();
        $addon->settings = self::getSettings();
        return $addon;
    }


    /**
     * Fetch addon template
     */
    public function fetchAddonResponse(string $template, ?string $block = null)
    {
        return $this->fetchResponse(self::getTemplatePath($template), $block);
    }
}
