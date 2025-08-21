<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.7
 *
 */

namespace HugaShop\Extensions;

use HugaShop\Models\Settings;
use HugaShop\Services\Config;
use HugaShop\Services\Helper;

trait BaseExtensionTrait
{

    /** 
     * Universal get extension name
     */
    private static function getName(?string $class = null)
    {
        // Получаем полное имя текущего класса
        $class = $class ?: static::class;

        // Приводим к нормальному виду (на всякий случай)
        $class = str_replace('/', '\\', $class);

        // Разбиваем по namespace
        $parts = explode('\\', $class);

        // Ищем "Extensions" и возвращаем следующий сегмент
        $index = array_search('Extensions', $parts);

        return ($index !== false && isset($parts[$index + 1]))
            ? $parts[$index + 1]
            : null;
    }


    /**
     * Get Extension settings
     */
    private static function getSettings(?string $param = null)
    {
        $extension_name = self::getName();
        $result = $settings = (object) (Settings::getParam($extension_name) ?? []);

        if (!is_null($param)) {
            $result = $settings->$param ?? null;
        }

        return $result;
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
     * Get Extension directory
     */
    public static function getExtensionDir()
    {
        return Config::get('extension_dir') . self::getName() . '/';
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
     * Get Extension
     */
    public function getExtension()
    {
        $extension = self::getConfig();
        $extension->settings = self::getSettings();
        return $extension;
    }


    /**
     * Fetch extension template
     */
    public function fetchExtResponse(string $template, ?string $block = null)
    {
        return $this->fetchResponse(self::getTemplatePath($template), $block);
    }
}
