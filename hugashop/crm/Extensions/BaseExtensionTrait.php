<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.4
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
    private function getSettings()
    {
        $extension_name = $this->getName();
        return  (object) (Settings::getParam($extension_name) ?? []);
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
        return $this->getExtensionDir() . $template;
    }


    /**
     * Has index function
     */
    public function hasIndex()
    {
        $ext_namespace = Extension::getNameSpace($this->getName());
        return $ext_namespace ? (method_exists($ext_namespace, 'index') ? true : false) : false;
    }


    /**
     * Fetcj extension template
     */
    public function fetchExtResponse(string $template, ?string $block = null)
    {
        return $this->fetchResponse($this->getExtensionDir() . 'templates/' . $template, $block);
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
}
