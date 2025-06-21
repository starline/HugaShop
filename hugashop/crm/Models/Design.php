<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 4.4
 *
 * Smarty 5.x require PHP7.4/PHP8.1
 * Commbine Plugin to collect and minimize css and js
 *
 * For debuging in Smarty 5.x use {$var|debug_print_var}
 * 
 * no escaping at all
 * {$myVar|raw}
 *
 */

namespace HugaShop\Models;

use Smarty\Smarty;
use HugaShop\Models\User\UserPermission;
use HugaShop\Models\Finance\FinanceCurrency;

class Design
{
    private static Smarty $smarty;
    public static ?string $theme;
    private static $Packages;
    public static $Translator;
    public static $locale;


    public static function getSmarty()
    {
        if (empty(self::$smarty)) {

            // Initialization Smarty
            self::$smarty = new Smarty();
            self::$smarty->setEscapeHtml(true);
        }

        return self::$smarty;
    }


    /**
     * Initilization Settings
     * @param array $theme
     */
    public static function initSettings(array $settings = [])
    {

        self::$theme = $settings['theme'] ?? Settings::getParam('theme');

        if (!empty($settings['packages'])) {
            self::$Packages = $settings['packages'];
        }

        self::getSmarty()->assign('theme', self::$theme);

        // Template
        self::getSmarty()->setTemplateDir(Config::get('templates_dir') . self::$theme . '/');

        // Caching
        self::getSmarty()->setCaching(Config::get('smarty')->caching);
        self::getSmarty()->setCacheLifetime(Config::get('smarty')->cache_lifetime);
        self::getSmarty()->setCacheDir(Config::get('compiled_dir') . self::$theme . '/cache');

        // Debugging
        // The debugging console does not work when you use the fetch() API, only when using display().
        self::getSmarty()->setDebugging(Config::get('smarty')->debugging);
        self::getSmarty()->setErrorReporting(E_ALL & ~E_NOTICE);

        // To make Smarty convert Errors warnings into Notices
        self::getSmarty()->muteUndefinedOrNullWarnings();

        // Compiling Smmarty Template
        self::getSmarty()->setCompileCheck(Config::get('smarty')->compile_check);
        self::getSmarty()->setCompileDir(Config::get('compiled_dir') . self::$theme);

        // Создаем папку для скомпилированных шаблонов текущей темы
        if (!is_dir(self::getSmarty()->getCompileDir())) {
            mkdir(self::getSmarty()->getCompileDir(), 0777, true);
        }

        // Make a folder for Cache
        if (!is_dir(self::getSmarty()->getCacheDir())) {
            mkdir(self::getSmarty()->getCacheDir(), 0777, true);
        }

        // Add Smarty Plugins
        self::setFunctionPlugin('url',               self::class,               'urlFunctionPlugin');
        self::setFunctionPlugin('extension',         self::class,               'extensionFunctionPlugin');
        self::setFunctionPlugin('setCSRF',           Request::class,            'setCSRF');
        self::setFunctionPlugin('getCSRFInput',      Request::class,            'getCSRFInput');

        self::setModifierPlugin('asset',             self::class,               'getAssetUrl');
        self::setModifierPlugin('resize',            Image::class,              'getURL');
        self::setModifierPlugin('plural',            self::class,               'plural_modifier');
        self::setModifierPlugin('first',             self::class,               'first_modifier');
        self::setModifierPlugin('cut',               self::class,               'cut_modifier');
        self::setModifierPlugin('byte_convert',      Helper::class,             'convertBytes');

        // DATE Pligins
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
    public static function getAssetUrl(string $asset_file, ?string $folder = null)
    {
        if (!empty($folder)) {
            $asset_file = $folder . '/' . $asset_file;
        } else {
            if (!empty(self::$theme)) {
                $asset_file = self::$theme . '/' . $asset_file;
            }
        }

        if (!empty(self::$Packages)) {
            return self::$Packages->getUrl($asset_file);
        } else {
            return $asset_file;
        }
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
        self::$Translator = $Translator;
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
     * Inject Extension
     * @param array $ext_params
     */
    public static function extensionFunctionPlugin(array $ext_params = [])
    {

        // Get Extensions by place
        if (!empty($place = $ext_params['place'])) {

            // Get All available extension for this place
            $place_extensions = Extension::getExtensionsByPlace($place);

            // Get template
            $place_ext_template = '';
            foreach ($place_extensions as $ext_name) {
                if (!empty($extension = Extension::makeExtension($ext_name))) {
                    Design::assign($extension->ext_name, $extension->ext_settings);
                    $place_ext_template .= $extension->{'get' . ucfirst(Helper::snakeToCamelCase($place)) . 'Template'}();
                }
            }
            return $place_ext_template;
        }

        // Get Extension by name
        if (isset($ext_params['name']) and !empty($extension = Extension::makeExtension($ext_params['name']))) {
            Design::assign($extension->ext_name, $extension->ext_settings);
            return $extension->getTemplate($ext_params);
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
    public static function plural_modifier($number, string $singular, string $plural_1, ?string $plural_2 = null)
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
    public static function first_modifier(array $params = [])
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
    public static function cut_modifier(array $array, $num = 1)
    {
        if ($num >= 0) {
            return array_slice($array, $num, count($array) - $num, true);
        } else {
            return array_slice($array, 0, count($array) + $num, true);
        }
    }
}
