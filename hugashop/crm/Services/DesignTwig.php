<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.5
 *
 */

namespace HugaShop\Services;

use Twig\Environment;
use HugaShop\Services\Config;
use Twig\Loader\ArrayLoader;
use HugaShop\Models\Settings;
use Twig\Loader\FilesystemLoader;

class DesignTwig
{
    public static $twig;
    public static $theme;
    public static $context = [];

    /**
     * Initilization Settings
     * @param array $theme
     */
    public static function initSettings(array $settings = [])
    {

        self::$theme = $settings['theme'] ?? Settings::getParam('theme');
        $cache_dir = Config::get('cache_dir') . self::$theme;

        // Создаем папку для скомпилированных шаблонов текущей темы
        if (!is_dir($cache_dir)) {
            mkdir($cache_dir, 0777, true);
        }

        $loader = new FilesystemLoader(Config::get('templates_dir') . self::$theme . '/');
        self::$twig = new Environment($loader, [
            'debug' => Config::get('smarty')->caching,
            'cache' => $cache_dir
        ]);
    }


    /**
     * Add varuables to the template
     * @param array|string $var
     * @param string $value
     */
    public static function assign(string|array $var, $value = null)
    {
        if (is_array($var)) {
            foreach ($var as $name => $value) {
                self::$context[$name] = $value;
            }
        } else {
            self::$context[$var] = $value;
        }

        return  self::$context;
    }


    /**
     * Add varuables to the template
     * @param array|string $var
     * @param string $value
     */
    public static function append(string $var, $value = null, ?int $index = null)
    {
        if (is_null($index)) {
            self::$context[$var][$index] = $value;
        } else {
            self::$context[$var][] = $value;
        }
        return self::$context[$var];
    }


    public static function render(string $template, array $context = [], string $template_dir = null)
    {
        $context = array_merge(self::$context, $context);
        return self::$twig->render($template, $context);
    }


    /**
     * Рендерим шаблон, вставляю переменные
     * @param string $template
     * @param array $params
     */
    public static function renderTemplate(string $template, array $params)
    {
        $loader = new ArrayLoader(['template.twig' => $template]);
        $twig = new Environment($loader);
        return $twig->render('template.twig', $params);
    }
}
