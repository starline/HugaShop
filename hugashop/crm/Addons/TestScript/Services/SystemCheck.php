<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.7
 *
 */

namespace HugaShop\Addons\TestScript\Services;

use HugaShop\Services\Config;

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


    /**
     * Log folders access check execution time.
     */
    public static function checkFoldersAccess(): void
    {
        $log_dir = Config::get('log_dir');

        if (empty($log_dir)) {
            return;
        }

        if (!is_dir($log_dir) && !mkdir($log_dir, 0775, true) && !is_dir($log_dir)) {
            return;
        }

        $log_file = $log_dir . 'check_folder.log';
        $timestamp = (new \DateTimeImmutable())->format('Y-m-d H:i:s');
        $message = sprintf('[%s] checkFoldersAccess triggered%s', $timestamp, PHP_EOL);

        file_put_contents($log_file, $message, FILE_APPEND | LOCK_EX);
    }
}
