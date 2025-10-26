<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 5.4
 *
 * Smarty 5.x require PHP7.4/PHP8.1
 *
 * For debuging in Smarty 5.x use {$var|debug_print_var}
 * 
 * no escaping at all
 * {$myVar|raw} {$myVar|dump}
 *
 */

namespace HugaShop\Services;

use Smarty\Smarty;
use HugaShop\Models\Image;
use HugaShop\Services\Addon;
use HugaShop\Models\Settings;
use HugaShop\Services\Config;
use HugaShop\Services\Helper;
use HugaShop\Services\Secure;
use HugaShop\Services\Request;
use HugaShop\Models\User\UserPermission;
use HugaShop\Models\Localization\Language;
use HugaShop\Models\Finance\FinanceCurrency;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Translation\Loader\YamlFileLoader;

class Design
{
    private static Smarty $smarty;
    public static ?string $theme;
    public static $Translator;
    private static $Packages;

    public static function getSmarty(): Smarty
    {
        if (empty(self::$smarty)) {

            // Initialization Smarty
            self::$smarty = new Smarty();
            self::$smarty->setEscapeHtml(true);

            // Set setting theme
            if (empty(self::$theme)) {
                self::setTheme();
            }

            self::setDefaultPlugins();
        }

        return self::$smarty;
    }


    /**
     * setDefaultPlugins
     */
    public static function setDefaultPlugins(): void
    {

        // Add Smarty Plugins
        self::setFunctionPlugin('url',               self::class,               'urlFunctionPlugin');
        self::setFunctionPlugin('addon',             self::class,               'addonFunctionPlugin');
        self::setFunctionPlugin('setCSRF',           Secure::class,             'setCSRF');
        self::setFunctionPlugin('getCSRFInput',      Secure::class,             'getCSRFInput');

        self::setModifierPlugin('asset',             self::class,               'getAssetUrl');
        self::setModifierPlugin('resize',            Image::class,              'getImageURL');
        self::setModifierPlugin('plural',            self::class,               'pluralModifier');
        self::setModifierPlugin('first',             self::class,               'firstModifier');
        self::setModifierPlugin('cut',               self::class,               'cutModifier');
        self::setModifierPlugin('byte_convert',      Helper::class,             'convertBytes');
        self::setModifierPlugin('dump',              self::class,               'dumpModifier');

        // DATE Plugins
        self::setModifierPlugin('date',              Helper::class,             'dateFormat');
        self::setModifierPlugin('time',              Helper::class,             'timeFormat');

        // Finance Plugins
        self::setModifierPlugin('price_convert',     FinanceCurrency::class,    'priceConvert');
        self::setModifierPlugin('price_html',        FinanceCurrency::class,    'priceHTML');
        self::setModifierPlugin('number',            Helper::class,             'numberFormat');

        // User Plugins
        self::setModifierPlugin('user_access',       UserPermission::class,     'checkAccess');

        // В новой версии Smarty php модификаторы необходимо регестрировать
        // Look Smarty_Security $php_functions
        self::setModifierPlugin('json_encode',   'json_encode');
        self::setModifierPlugin('join',          'join');
        self::setModifierPlugin('strtotime',     'strtotime');
        self::setModifierPlugin('is_null',       'is_null');
        self::setModifierPlugin('urlencode',     'urlencode');
        self::setModifierPlugin('ceil',          'ceil');
        self::setModifierPlugin('floor',         'floor');
        self::setModifierPlugin('max',           'max');
        self::setModifierPlugin('min',           'min');
    }


    /**
     * setTheme
     */
    public static function setTheme(?string $theme = null)
    {

        self::$theme    = $theme ?: Settings::getParam('theme');
        $smarty         = self::getSmarty();
        $config         = Config::get('smarty');
        $compiled_dir   = Config::get('compiled_dir') . self::$theme;

        // Assign theme var
        $smarty->assign('theme', self::$theme);

        // Template
        $smarty->setTemplateDir(Config::get('templates_dir') . self::$theme . '\/html\/');

        // Caching
        $smarty->setCaching($config->caching);
        $smarty->setCacheLifetime($config->cache_lifetime);
        $smarty->setCacheDir($compiled_dir . '/cache');

        // Debugging
        // The debugging console does not work when you use the fetch() API, only when using display().
        $smarty->setDebugging($config->debugging);
        $smarty->setErrorReporting(E_ALL & ~E_NOTICE);

        // To make Smarty convert Errors warnings into Notices
        $smarty->muteUndefinedOrNullWarnings();

        // Compiling Smmarty Template
        $smarty->setCompileCheck($config->compile_check);
        $smarty->setCompileDir($compiled_dir);

        // Создаем папку для скомпилированных шаблонов текущей темы
        $compile_dir = $smarty->getCompileDir();
        if (!is_dir($compile_dir)) {
            mkdir($compile_dir, 0777, true);
        }

        // Make a folder for Cache
        $cache_dir = $smarty->getCacheDir();
        if (!is_dir($cache_dir)) {
            mkdir($cache_dir, 0777, true);
        }
    }


    /**
     *  Get theme 
     */
    public static function getTheme(): ?string
    {
        return self::$theme ?: null;
    }


    /**
     * Add varuables to the template
     * @param array|string $var
     * @param string $value
     */
    public static function assign(string|array $var, $value = null)
    {
        return self::getSmarty()->assign($var, $value);
    }


    /**
     * Append variables to the template array
     * @param string|array $var
     * @param string $value
     * @param string $index
     */
    public static function append(string|array $var, ?string $value = null, ?string $index = null)
    {
        return self::getSmarty()->append($var, $value, $index);
    }


    /**
     * Clear template variable
     */
    public static function clearAssign(string|array $var)
    {
        return self::getSmarty()->clearAssign($var);
    }


    /**
     * Get the Smarty template
     * @param string $template
     * @param ?string $template_dir
     */
    public static function fetch(string $template, ?string $template_dir = null)
    {
        if (!empty($template_dir)) {
            self::setTemplateDir($template_dir);
        }
        return self::getSmarty()->fetch($template);
    }


    /**
     * Check if template exists
     */
    public static function templateExists(string $tempalte): bool
    {
        $template_path = self::getSmarty()->getTemplateDir(0) . $tempalte;
        return file_exists($template_path);
    }


    /**
     * sett Flash messag
     * @param string $type
     */
    public static function setFlashMessage(string $type, $response)
    {
        if ($type == 'add') {
            if (!empty($response)) {
                Request::setSession('message_success', 'added');
            } else {
                Request::setSession('message_error', 'not_added');
            }
        }

        if ($type == 'update') {
            if (!empty($response)) {
                Request::setSession('message_success', 'updated');
            } else {
                Request::setSession('message_empty', 'not_updated');
            }
        }

        if ($type == 'delete') {
            if (!empty($response)) {
                Request::setSession('message_success', 'deleted');
            } else {
                Request::setSession('message_error', 'not_deleted');
            }
        }

        if ($type == 'error') {
            Request::setSession('message_error', $response);
        }

        return $response;
    }


    /**
     * Set Tempalte dir
     * @param string @dir
     */
    public static function setTemplateDir(string $dir): void
    {
        self::getSmarty()->setTemplateDir($dir);
    }


    /**
     * Set Compiled dir
     * @param string @dir
     */
    public static function setCompiledDir(string $dir): void
    {
        self::getSmarty()->setCompileDir($dir);
    }


    /**
     * Set Cache dir
     * @param string $dir
     */
    public static function setCacheDir(string $dir): void
    {
        self::getSmarty()->setCacheDir($dir);
    }


    /**
     * getAssetUrl
     * @param string $asset_file
     * @param ?string $folder
     */
    public static function getAssetUrl(string $asset_file, ?string $folder = null, ?string $type = null)
    {

        if (!empty($folder)) {
            if ($type === 'addon') {
                $asset_file = trim($folder, '/') . '/templates/assets/' . ltrim($asset_file, '/');
            } else {
                $asset_file = rtrim($folder, '/') . '/' . ltrim($asset_file, '/');
            }
        } elseif (!empty(self::$theme)) {
            $asset_file =  self::$theme . '/assets/' . ltrim($asset_file, '/');
        }

        if (!empty(self::$Packages)) {
            return self::$Packages->getUrl($asset_file);
        }

        return $asset_file;
    }


    /**
     *  Get var from Smarty template
     *  @param string $name
     */
    public static function getTemplateVars(string $name)
    {
        return self::getSmarty()->getTemplateVars($name);
    }


    /**
     * Set Translator
     * @param string $locale
     * @param string $theme
     */
    public static function setTranslator($Translator)
    {
        if (empty(self::$theme)) {
            return;
        }

        self::$Translator = $Translator;

        $locale_code = Language::getCurrent()->code;
        $main_code   = Language::getMain()->code;

        // Add Lovale translation file
        $translate_file_path = Config::get('templates_dir') . self::$theme . '/translations/messages.' . $locale_code . '.yaml';
        if (file_exists($translate_file_path)) {
            self::$Translator->addResource('yaml', $translate_file_path, $locale_code);
        }

        // If not main locale, set fallback
        if ($locale_code != $main_code) {
            self::$Translator->setFallbackLocales([$main_code]);

            $fallback_translate_file_path = Config::get('templates_dir') . self::$theme . '/translations/messages.' . $main_code . '.yaml';
            if (file_exists($fallback_translate_file_path)) {
                self::$Translator->addResource('yaml', $fallback_translate_file_path, $locale_code);
            }
        }

        // Сохранять список ресурсов по локали в кеш, проверять какие уже загруженны и не загружать заново.
        $catalogue = self::$Translator->getCatalogue($locale_code);
        dump($catalogue = $catalogue->getResources());

        self::setModifierPlugin('trans', self::$Translator, 'trans');
    }


    /**
     * Set Packages
     */
    public static function setPackages($Packages)
    {
        self::$Packages = $Packages;
    }


    /**
     * Set Plugin
     * @param string @name
     * @param $instance
     * @param ?string $function_name
     */
    public static function setModifierPlugin(string $name, $instance, ?string $function_name = null): void
    {
        $callback = $function_name ? [$instance, $function_name] : $instance;
        self::getSmarty()->registerPlugin(Smarty::PLUGIN_MODIFIER, $name, $callback);
    }


    /**
     * Set Plugin
     * @param string @name
     */
    public static function setFunctionPlugin(string $name, $instance, ?string $function_name = null): void
    {
        $callback = $function_name ? [$instance, $function_name] : $instance;
        self::getSmarty()->registerPlugin(Smarty::PLUGIN_FUNCTION, $name, $callback);
    }


    /**
     * Inject Addon
     * @param array $ext_params
     */
    public static function addonFunctionPlugin(array $ext_params = [])
    {

        // Get Addons by place
        if (!empty($place = $ext_params['place'])) {

            // Get All available addon for this place
            $place_addons = Addon::getAddonsByPlace($place);

            // Get template
            $place_ext_template = '';
            foreach ($place_addons as $ext_name) {
                if (!empty($addon = Addon::getNameSpace($ext_name))) {
                    Design::assign($addon::getName(), $addon::getSettings());
                    $get_method = 'get' . ucfirst(Helper::snakeToCamelCase($place)) . 'Template';
                    if (method_exists($addon, $get_method)) {
                        $place_ext_template .= $addon::$get_method();
                    }
                }
            }
            return $place_ext_template;
        }

        // Get Addon by name
        if (isset($ext_params['name']) and !empty($addon = Addon::getNameSpace($ext_params['name']))) {
            Design::assign($addon::getName(), $addon::getSettings());
            return $addon::getTemplate($ext_params);
        }

        return false;
    }


    /**
     * Making URL with params
     * @param array $params
     */
    public static function urlFunctionPlugin(array $params)
    {

        // Get clear paramm and unset it
        $clear = false;
        if (isset($params['clear'])) {
            if ($params['clear'] === true) {
                $clear = true;
            }
            unset($params['clear']);
        }

        if (is_array(reset($params))) {
            return Request::url(reset($params), $clear);
        } else {
            return Request::url($params, $clear);
        }
    }


    /**
     * Plural
     * Smarty Plugin
     * Example: $count|plural:'товар':'товаров':'товара'
     * @param $number
     * @param string $singular
     * @param string $plural1
     * @param string $plural2
     */
    public static function pluralModifier($number, string $singular, string $plural_1, ?string $plural_2 = null)
    {
        if (is_null($number)) {
            return $singular;
        }

        $number = abs($number); # Absolute value
        if (!empty($plural_2)) {
            $p1 = $number % 10;
            $p2 = $number % 100;
            if ($number == 0) {
                return $plural_1;
            }
            if ($p1 == 1 && !($p2 >= 11 && $p2 <= 19)) {
                return $singular;
            } elseif ($p1 >= 2 && $p1 <= 4 && !($p2 >= 11 && $p2 <= 19)) {
                return $plural_2;
            } else {
                return $plural_1;
            }
        } else {
            if ($number == 1) {
                return $singular;
            } else {
                return $plural_1;
            }
        }
    }


    /**
     * Take fist value of array
     * @param array $params
     */
    public static function firstModifier(array $params = [])
    {
        if (!is_array($params)) {
            return false;
        }
        return reset($params);
    }


    /**
     * Cut Array
     * cut удаляет первую фотографию, если нужно начать c 2-й - пишем cut:2 и тд
     * @param array $array
     * @param int $num
     */
    public static function cutModifier(array $array, $num = 1)
    {
        if ($num >= 0) {
            return array_slice($array, $num, count($array) - $num, true);
        } else {
            return array_slice($array, 0, count($array) + $num, true);
        }
    }


    /**
     * Dump
     */
    public static function dumpModifier($var)
    {
        dump($var);
    }
}
