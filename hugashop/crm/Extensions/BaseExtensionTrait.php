<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.3
 *
 */

namespace HugaShop\Extensions;

use HugaShop\Models\Settings;
use HugaShop\Services\Config;

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
     * Get Extension directory
     */
    public function getExtensionDir()
    {
        return Config::get('extension_dir') . $this->getName() . '/';
    }
}
