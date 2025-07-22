<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 3.4
 *
 * Класс-обертка для обращения к переменным $_GET, $_POST, $_FILES, $_COOKIE, $_SESSION
 *
 */

namespace HugaShop\Services;

use HugaShop\Models\Settings;
use HugaShop\Services\Config;
use App\Services\LockEditService;
use HugaShop\Models\User\UserPermission;

class Request
{
    private static $sessionDriver;
    public static $time_start;

    /**
     * Определение request-метода обращения к странице (GET, POST)
     * Если задан аргумент функции (название метода, в любом регистре), возвращает true или false
     * Если аргумент не задан, возвращает имя метода
     * Пример:
     *
     *	if(Request::method('POST'))
     *		print 'Request method is POST';
     *
     */
    public static function method(?string $method = null)
    {
        if (!empty($method)) {
            return strtolower($_SERVER['REQUEST_METHOD']) == strtolower($method);
        }
        return $_SERVER['REQUEST_METHOD'];
    }


    /**
     * Define a parameter
     */
    public static function has($key)
    {
        return array_key_exists($key, $_GET) || array_key_exists($key, $_POST);
    }


    /**
     * Возвращает variable из методов GET и POST
     * @param string $var_name
     * @param string $type
     */
    public static function input(?string $var_name = null, ?string $type = null)
    {
        $val = null;
        $val = self::post($var_name, $type);
        if (empty($val)) {
            $val = self::get($var_name, $type);
        }
        return $val;
    }


    /**
     * Возвращает переменную _GET, отфильтрованную по заданному типу, если во втором параметре указан тип фильтра
     * Если $type не задан, возвращает переменную в чистом виде
     * @param $name
     * @param $type
     */
    public static function get($name = null, $type = null)
    {
        // Returne all $_GET values
        if (is_null($name) and is_null($type)) {
            return self::gets();
        }

        $val = null; # если переменная не задана возвращаем null
        if (!empty($name) and isset($_GET[$name])) {
            $val = $_GET[$name];
            if (!is_null($type)) {
                $val = self::getValueByType($val, $type);
            }
        }

        return $val;
    }


    /**
     * Get int $_GET
     */
    public static function getInt(string $name)
    {
        return self::get($name, 'int');
    }


    /**
     * Выбираем все значения $_GET
     */
    public static function gets(): array
    {
        $res = [];
        if (!empty($_GET)) {
            foreach ($_GET as $p_name => $p_data) {
                $res[$p_name] = $p_data;
            }
        }
        return $res;
    }


    /**
     * Возвращает переменную $_POST, отфильтрованную по заданному типу, если во втором параметре указан тип фильтра
     * Если $type не задан, возвращает переменную в чистом виде
     * Если переменной не существует в $_POST, возвращаем NULL
     * @param $name
     * @param $type
     */
    public static function post($name = null, string|null $type = null)
    {

        // Returne all $_POST values
        if (is_null($name) and is_null($type)) {
            return self::posts();
        }

        $val = null;
        if (!empty($name)) {
            if (isset($_POST[$name])) {
                $val = $_POST[$name];
            }
            if (!is_null($type)) {
                $val = self::getValueByType($val, $type);
            }
        } elseif (empty($name)) {
            $val = file_get_contents('php://input');
        }

        //echo $name."=".$val.'<br>';

        return $val;
    }


    /**
     * Get int $_GET
     */
    public static function postInt(string $name)
    {
        return self::post($name, 'int');
    }

    /**
     * Выбираем все значения $_POST
     */
    public static function posts()
    {
        $res = null;

        if (!empty($_POST)) {
            foreach ($_POST as $p_name => $p_data) {
                $res[$p_name] = $p_data;
            }
        }

        return $res;
    }


    /**
     * Перобразовываем переменные согласно их типу
     * @param string|null|array|int $val
     * @param string $type
     */
    public static function getValueByType(string|null|array|int $val, string $type)
    {

        // Массив (автоопределение)
        if (is_array($val) and $type != 'array') {
            reset($val);
            return $val;
        }

        // Строка
        if (in_array($type, ['string', 'varchar', 'text', 'mediumtext', 'datetime', 'date'])) {
            return strval($val);
        }

        // Целое число
        if (in_array($type, ['int', 'integer', 'bigint'])) {

            // Преобразуем строки в NULL
            if ($val == 'null' || $val == '' || is_null($val)) {
                return null;
            } else {
                return intval($val);
            }
        }

        // Число с плавающей запятой
        if (in_array($type, ['float', 'decimal'])) {

            // заменим запятые на точку
            $val = str_replace(',', '.', $val);
            return floatval($val);
        }

        // Array
        if ($type == 'array') {
            $val_arr = [];  # if empty or null
            if (is_array($val)) { # if array
                $val_arr = $val;
            } elseif (!empty($val)) { # if not array and not empty
                $val_arr[] = $val;
            }
            return $val_arr;
        }

        // Boolean 1|0
        if (in_array($type, ['bool', 'boolean', 'tinyint'])) {
            return empty($val) ? 0 : 1;
        }
    }


    /**
     * Возвращает переменную $_FILES
     * Обычно переменные $_FILES являются двухмерными массивами, поэтому можно указать второй параметр,
     * например, чтобы получить имя загруженного файла: $filename = Request::files('myfile', 'name');
     */
    public static function files($name, $name2 = null)
    {
        if (!empty($name2) && !empty($_FILES[$name][$name2])) {
            return $_FILES[$name][$name2];
        } elseif (empty($name2) && !empty($_FILES[$name])) {
            return $_FILES[$name];
        } else {
            return null;
        }
    }


    /**
     * Рекурсивная чистка магических слешей
     * @param $var
     */
    public function stripslashes_recursive($var)
    {
        $res = [];
        if (is_array($var)) {
            foreach ($var as $k => $v) {
                $res[stripcslashes($k)] = $this->stripslashes_recursive($v);
            }
        } else {
            $res = stripcslashes($var);
        }

        return $res;
    }


    /**
     * Creat URL
     * @param array $params - переменные
     * @param bool $clear - зачищает пустые переменные
     */
    public static function url(array $params = [], bool $clear = false)
    {
        $url = @parse_url($_SERVER["REQUEST_URI"]);

        $query = array();
        if (isset($url['query']) and !$clear) {
            parse_str($url['query'], $query);
        }

        foreach ($query as &$v) {
            if (!is_array($v)) {
                $v = stripslashes(urldecode($v));
            }
        }

        foreach ($params as $name => $value) {
            $query[$name] = $value;
        }

        // Check empty value
        foreach ($query as $name => $value) {
            if ($value === '' or $value === null) { # При не срогом сравнение '' = 0
                unset($query[$name]);
            }
        }

        if (!empty($query)) {
            $url['query'] = http_build_query($query);
        } else {
            $url['query'] = null;
        }

        $result = http_build_url("", $url);
        return $result;
    }


    /**
     * CSRF protect
     * Get input for FORM
     */
    public static function getCSRFInput()
    {
        $token = self::setCSRF();
        return '<input type="hidden" name="csrf" value="' . $token . '">';
    }


    /**
     * CSRF protect
     * Set token
     */
    public static function setCSRF()
    {
        if (empty(self::getSession('csrf'))) {
            self::setSession('csrf', bin2hex(random_bytes(16))); # random token
        }
        return self::getSession('csrf');
    }


    /**
     * CSRF protect
     * Check token
     */
    public static function checkCSRF(): bool
    {
        if (!self::method('post') and !self::method('get')) {
            return false;
        }

        if (empty(self::getSession('csrf')) || empty(self::input('csrf'))) {
            return false;
        }

        if (self::getSession('csrf') != self::input('csrf')) {
            return false;
        }

        return true;
    }


    /**
     * XSS protect
     * @param $text
     */
    public static function escape(string $text)
    {
        return htmlspecialchars($text);
    }


    /**
     * Собираем + Проверяем данные и разрешения на редактирование POST переменных
     * Пример $fillable_params:
     *  type         - varchar|int|tinyint|text|decimal|datetime|date
     *  length       - 11|255|1|10.2
     *  required     - true
     *  access       - false|user
     *
     * @param array $fillable_params
     */
    public static function getInputAcces(array $fillable_params)
    {
        if (!self::method('post')) {
            return null;
        }

        // Check CSRF
        if (!self::checkCSRF()) {
            return null;
        }

        $res = new \stdClass();
        $decline = false;

        foreach ($fillable_params as $param_name => $param_data) {

            // Empty required param
            if (!empty($param_data['required']) || !empty($param_data['req'])) {
                if (empty(Request::post($param_name, $param_data['type']))) {
                    Design::append('service_messages_empty', $param_name);
                    Design::append('form_invalid', $param_name);
                    $decline = true;
                }
            }

            // Если есть права на редактирование переменной
            if (isset($param_data['access'])) {
                if ($param_data['access'] === false || !UserPermission::checkAccess($param_data['access'])) {
                    continue;
                }
            }

            // Если переменная передана POST или checkbox(boolean|tinyint), добавляем в Object
            if (isset($_POST[$param_name]) || (isset($param_data['type']) and in_array($param_data['type'], ['boolean', 'bool', 'tinyint']))) {
                $res->$param_name = Request::post($param_name, $param_data['type']);

                // Triming varchar
                if ($param_data['type'] == 'varchar') {
                    $res->$param_name = trim($res->$param_name);

                    // Cut string by MySQL length if specified
                    if (!empty($param_data['length']) && is_string($res->$param_name)) {
                        $maxLen = (int) $param_data['length'];
                        $res->$param_name = mb_substr($res->$param_name, 0, $maxLen);
                    }
                }
            }
        }

        if ($decline === true) {
            return null;
        }

        return $res;
    }


    /**
     * Get post data and check edit locked
     */
    public static function getInputCheckEditAccess(string $model_name, ?int $item_id = null)
    {
        if (LockEditService::isEditLocked($model_name, $item_id)) {
            return null;
        }

        return self::getInputAcces($model_name::getFields());
    }


    /**
     * Set COOKIE
     * @param string $name
     * @param string $data
     * @param int $days
     * @param string $dir
     * @param bool $prefix
     */
    public static function setCookie(string $name, string $data, int $days = 360, string $dir = '/', bool $prefix = true): void
    {
        $domain = '.' . Settings::getParam('domain'); # set .domain.com

        if ($prefix === true) {
            $name =  Config::get('cookie_prefix') . $name;
            $domain = ''; # set domain.com
        }

        setcookie(
            $name,
            $data,
            time() + 3600 * 24 * $days, # how long
            $dir, # catalog
            $domain
        );
    }


    /**
     * Get COOKIE
     * @param string $name
     * @param bool $prefix
     */
    public static function getCookie(string $name, bool $prefix = true): ?string
    {
        if ($prefix === true) {
            $name = Config::get('cookie_prefix') . $name;
        }

        if (isset($_COOKIE[$name])) {
            return $_COOKIE[$name];
        }

        return null;
    }


    /**
     * Delete COOKIE
     * @param string $name
     */
    public static function deleteCookie(string $name): void
    {
        self::setCookie($name, '', -1); # shuld set Null for domain
        unset($_COOKIE[Config::get('cookie_prefix') . $name]);
    }


    /**
     * Set SESSION
     * @param string $name
     * @param string $data
     * @param bool $prefix
     */
    public static function setSession(string $name, string|array $data, bool $prefix = true): void
    {
        self::startSession();

        $name = $prefix ? Config::get('cookie_prefix') . $name : $name;

        if (!empty(self::$sessionDriver)) {
            self::$sessionDriver->set($name, $data);
        } else {
            $_SESSION[$name] = $data;
        }
    }


    /**
     * Get SESSION
     * @param string $name
     * @param bool $prefix
     */
    public static function getSession(string $name, bool $prefix = true): string|array|null
    {
        self::startSession();

        $name = $prefix ? Config::get('cookie_prefix') . $name : $name;

        if (!empty(self::$sessionDriver)) {
            return self::$sessionDriver->get($name);
        } else {
            if (isset($_SESSION[$name])) {
                return $_SESSION[$name];
            }
        }

        return null;
    }


    /**
     * Delete SESSION
     * @param string $name
     * @param bool $prefix
     */
    public static function deleteSession(string|array $name, bool $prefix = true): void
    {
        self::startSession();

        $name = $prefix ? Config::get('cookie_prefix') . $name :  $name;

        if (!empty(self::$sessionDriver)) {
            self::$sessionDriver->remove($name);
        } else {
            unset($_SESSION[$name]);
        }
    }


    /**
     * Check if session is available and Start session
     * @param object $sessionDriver
     */
    public static function startSession(object|null $sessionDriver = null): bool
    {
        if (empty(self::$time_start)) {
            self::$time_start = hrtime(true);
        }

        if (!empty(self::$sessionDriver)) {
            return true;
        }

        if (is_null($sessionDriver)) {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
                return true;
            }
        } else {
            self::$sessionDriver = $sessionDriver;
            return true;
        }

        return false;
    }


    /**
     * isAjax
     * @return bool
     */
    public static function isAjax(): bool
    {
        if (
            (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')
            || isset($_SERVER['HTTP_HX_REQUEST'])
        ) {
            return true;
        }
        return false;
    }


    /**
     * Make redirect
     *
     * 300 - Multiple Choices
     * 301 - Moved Permanently
     * 302 - Found (default)
     * 303 - See Other
     * 304 - Not Modified
     * 305 - Use Proxy
     * 306 - Switch Proxy
     * 307 - Temporary Redirect
     * 308 - Permanent Redirect
     *
     * @param string $redirect_url
     * @param string $redirect_type
     */
    public static function makeRedirect(string $redirect_url, string $redirect_type = '302'): void
    {
        switch ($redirect_type) {
            case '301':
                header('HTTP/1.1 301 Moved Permanently');
                break;
            case '302':
                header('HTTP/1.1 302 Found');
                break;
        }

        header('location: ' . $redirect_url);
        exit();
    }


    /**
     * Set current page
     * Save last viewed page
     */
    public static function setCurrentPage()
    {
        if (empty(self::getSession('current_page')) || (!empty(self::getSession('current_page')) and self::getSession('current_page') !== $_SERVER['REQUEST_URI'])) {
            if (!empty(self::getSession('current_page'))) {
                self::setSession('last_visited_page', self::getSession('current_page'));
            }
            self::setSession('current_page', $_SERVER['REQUEST_URI']);
        }
    }
}







if (!function_exists('http_build_url')) {
    define('HTTP_URL_REPLACE', 1);                  // Replace every part of the first URL when there's one of the second URL
    define('HTTP_URL_JOIN_PATH', 2);                // Join relative paths
    define('HTTP_URL_JOIN_QUERY', 4);               // Join query strings
    define('HTTP_URL_STRIP_USER', 8);               // Strip any user authentication information
    define('HTTP_URL_STRIP_PASS', 16);              // Strip any password authentication information
    define('HTTP_URL_STRIP_AUTH', 32);              // Strip any authentication information
    define('HTTP_URL_STRIP_PORT', 64);              // Strip explicit port numbers
    define('HTTP_URL_STRIP_PATH', 128);             // Strip complete path
    define('HTTP_URL_STRIP_QUERY', 256);            // Strip query string
    define('HTTP_URL_STRIP_FRAGMENT', 512);         // Strip any fragments (#identifier)
    define('HTTP_URL_STRIP_ALL', 1024);             // Strip anything but scheme and host

    /**
     * Build an URL
     * The parts of the second URL will be merged into the first according to the flags argument.
     *
     * @param	mixed			(Part(s) of) an URL in form of a string or associative array like parse_url() returns
     * @param	mixed			Same as the first argument
     * @param	int				A bitmask of binary or'ed HTTP_URL constants (Optional)HTTP_URL_REPLACE is the default
     * @param	array			If set, it will be filled with the parts of the composed url like parse_url() would return
     *
     */
    function http_build_url($url = "", $parts = array(), $flags = HTTP_URL_REPLACE, &$new_url = false)
    {
        $keys = array('user', 'pass', 'port', 'path', 'query', 'fragment');

        // HTTP_URL_STRIP_ALL becomes all the HTTP_URL_STRIP_Xs
        if ($flags & HTTP_URL_STRIP_ALL) {
            $flags |= HTTP_URL_STRIP_USER;
            $flags |= HTTP_URL_STRIP_PASS;
            $flags |= HTTP_URL_STRIP_PORT;
            $flags |= HTTP_URL_STRIP_PATH;
            $flags |= HTTP_URL_STRIP_QUERY;
            $flags |= HTTP_URL_STRIP_FRAGMENT;
        }
        // HTTP_URL_STRIP_AUTH becomes HTTP_URL_STRIP_USER and HTTP_URL_STRIP_PASS
        elseif ($flags & HTTP_URL_STRIP_AUTH) {
            $flags |= HTTP_URL_STRIP_USER;
            $flags |= HTTP_URL_STRIP_PASS;
        }

        // Parse the original URL
        $parse_url = parse_url($url);

        // Scheme and Host are always replaced
        if (isset($parts['scheme'])) {
            $parse_url['scheme'] = $parts['scheme'];
        }
        if (isset($parts['host'])) {
            $parse_url['host'] = $parts['host'];
        }

        // (If applicable) Replace the original URL with it's new parts
        if ($flags & HTTP_URL_REPLACE) {
            foreach ($keys as $key) {
                if (isset($parts[$key])) {
                    $parse_url[$key] = $parts[$key];
                }
            }
        } else {

            // Join the original URL path with the new path
            if (isset($parts['path']) && ($flags & HTTP_URL_JOIN_PATH)) {
                if (isset($parse_url['path'])) {
                    $parse_url['path'] = rtrim(str_replace(basename($parse_url['path']), '', $parse_url['path']), '/') . '/' . ltrim($parts['path'], '/');
                } else {
                    $parse_url['path'] = $parts['path'];
                }
            }

            // Join the original query string with the new query string
            if (isset($parts['query']) && ($flags & HTTP_URL_JOIN_QUERY)) {
                if (isset($parse_url['query'])) {
                    $parse_url['query'] .= '&' . $parts['query'];
                } else {
                    $parse_url['query'] = $parts['query'];
                }
            }
        }

        // Strips all the applicable sections of the URL
        // Note: Scheme and Host are never stripped
        foreach ($keys as $key) {
            if ($flags & (int)constant('HTTP_URL_STRIP_' . strtoupper($key))) {
                unset($parse_url[$key]);
            }
        }


        $new_url = $parse_url;

        return ((isset($parse_url['scheme'])) ? $parse_url['scheme'] . '://' : '')
            . ((isset($parse_url['user'])) ? $parse_url['user'] . ((isset($parse_url['pass'])) ? ':' . $parse_url['pass'] : '') . '@' : '')
            . ((isset($parse_url['host'])) ? $parse_url['host'] : '')
            . ((isset($parse_url['port'])) ? ':' . $parse_url['port'] : '')
            . ((isset($parse_url['path'])) ? $parse_url['path'] : '')
            . ((isset($parse_url['query'])) ? '?' . $parse_url['query'] : '')
            . ((isset($parse_url['fragment'])) ? '#' . $parse_url['fragment'] : '');
    }
}


if (!function_exists('http_build_query')) {
    function http_build_query($data, $prefix = null, $sep = '', $key = '')
    {
        $ret    = array();
        foreach ((array)$data as $k => $v) {
            $k    = urlencode($k);
            if (is_int($k) && $prefix != null) {
                $k    = $prefix . $k;
            }
            if (!empty($key)) {
                $k    = $key . "[" . $k . "]";
            }

            if (is_array($v) || is_object($v)) {
                array_push($ret, http_build_query($v, "", $sep, $k));
            } else {
                array_push($ret, $k . "=" . urlencode($v));
            }
        }

        if (empty($sep)) {
            $sep = ini_get("arg_separator.output");
        }

        return join($sep, $ret);
    }
}
