<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.6
 *
 */

namespace HugaShop\Extensions\TestScript\Services;

final class SystemCheck
{

    public static function checkPhp()
    {
        $php_check = new \stdClass();

        $php_check->version = phpversion();

        if (extension_loaded('apc') && ini_get('apc.enabled')) {
            $php_check->apc = ini_get('apc.shm_size');
        }

        $php_check->default_charset = ini_get('default_charset');
        $php_check->short_open_tag  = ini_get('short_open_tag');
        $php_check->display_errors  = ini_get('display_errors');

        // func_overload
        $php_check->func_overload = ini_get('mbstring.func_overload');

        // 1 - если работает func_overload(2)
        // 2 - если func_overload(0)
        $length = strlen("\xd0\xa2");
        if ($length != 1 and $php_check->func_overload == 2) {
            $php_check->func_overload = "str function doesn't overload to mbstring";
        }
        return $php_check;
    }
}
