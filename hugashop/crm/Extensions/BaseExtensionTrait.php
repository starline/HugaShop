<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.6
 *
 */

namespace HugaShop\Extensions;

use HugaShop\Models\Settings;
use HugaShop\Services\Config;
use HugaShop\Services\Helper;
use HugaShop\Services\Extension;

trait BaseExtensionTrait
{

    /** 
     * Universal get extension name
     */
    private function getName(?string $class = null)
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
    private function getSettings(?string $param = null)
    {
        $extension_name = $this->getName();
        $result = $settings = (object) (Settings::getParam($extension_name) ?? []);

        if (!is_null($param)) {
            $result = $settings->$param ?? null;
        }

        return $result;
    }


    /**
     * Get Extension config
     */
    public function getConfig(?string $param = null)
    {
        $config = Helper::getModule($this->getName(), Config::get('extension_dir'));

        if (is_null($param)) {
            return $config;
        }
        return $config->$param ?? null;
    }


    /**
     * Get Extension directory
     */
    public function getExtensionDir()
    {
        return Config::get('extension_dir') . $this->getName() . '/';
    }


    /**
     * Get extension template
     * @param string $template
     */
    public function getTemplatePath(string $template)
    {
        return $this->getExtensionDir() . 'templates/' . $template;
    }


    /**
     * Fetch extension template
     */
    public function fetchExtResponse(string $template, ?string $block = null)
    {
        return $this->fetchResponse($this->getTemplatePath($template), $block);
    }


    /**
     * Get Extension
     */
    public function getExtension()
    {
        $extension = $this->getConfig();
        $extension->settings = $this->getSettings();
        $extension->hasIndex = $this->hasIndex();
        return $extension;
    }


    /**
     * Has index function
     */
    public function hasIndex()
    {
        $ext_namespace = Extension::getNameSpace($this->getName());
        return $ext_namespace ? (method_exists($ext_namespace, 'index') ? true : false) : false;
    }
}
