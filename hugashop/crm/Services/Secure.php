<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.1
 *
 */

namespace HugaShop\Services;

use App\Services\LockEditService;
use HugaShop\Models\User\UserPermission;

class Secure
{

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
        if (empty(Request::getSession('csrf'))) {
            Request::setSession('csrf', bin2hex(random_bytes(16))); # random token
        }
        return Request::getSession('csrf');
    }


    /**
     * CSRF protect
     * Check token
     */
    public static function checkCSRF(): bool
    {
        if (!Request::method('post') and !Request::method('get')) {
            return false;
        }

        if (empty(Request::getSession('csrf')) || empty(Request::input('csrf'))) {
            return false;
        }

        if (Request::getSession('csrf') != Request::input('csrf')) {
            return false;
        }

        return true;
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
    public static function getInputAcces(array $fillable_params, array $exclude = [])
    {
        if (!Request::method('post')) {
            return;
        }

        // Check CSRF
        if (!self::checkCSRF()) {
            return;
        }

        $res = new \stdClass();
        $decline = false;

        foreach ($fillable_params as $param_name => $param_data) {
            if (in_array($param_name, $exclude)) {
                continue;
            }

            // Empty required param
            if (!empty($param_data['required']) || !empty($param_data['req'])) {
                if (Request::has($param_name) and empty(Request::post($param_name, $param_data['type']))) {
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

            // Если переменная передана POST
            if (isset($_POST[$param_name])) {
                $res->$param_name = Request::post($param_name, $param_data['type']);

                // Triming varchar
                if ($param_data['type'] == 'varchar' || $param_data['type'] == 'string') {
                    $trim = $param_data['trim'] ?? true;
                    if ($trim) {
                        $res->$param_name = trim($res->$param_name);
                    }

                    // Cut string by MySQL length if specified
                    if (!empty($param_data['length']) && is_string($res->$param_name)) {
                        $maxLen = (int) $param_data['length'];
                        $res->$param_name = mb_substr($res->$param_name, 0, $maxLen);
                    }
                }
            }
        }

        if ($decline === true) {
            return;
        }

        return $res;
    }


    /**
     * Get post data and check edit locked
     */
    public static function getInputCheckEditAccess(string $model_name, ?int $item_id = null, array $exclude = [])
    {
        if (LockEditService::isEditLocked($model_name, $item_id)) {
            return null;
        }

        return self::getInputAcces($model_name::getFields(), $exclude);
    }
}
