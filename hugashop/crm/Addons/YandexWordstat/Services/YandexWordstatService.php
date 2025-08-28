<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.1
 * 
 * @link https://yandex.com/support2/wordstat/ru/content/api-wordstat
 * @link https://yandex.com/support2/wordstat/ru/content/api-structure
 *
 */

namespace HugaShop\Addons\YandexWordstat\Services;

use HugaShop\Addons\BaseAddonTrait;

class YandexWordstatService
{

    use BaseAddonTrait;

    /**
     * Create base chat
     */
    public static function getKewords($keyword, $language = 'ru', $region = 225)
    {
        $auth_token = self::getSettings()?->auth_token;
        $client_id = self::getSettings()?->client_id;

        if (empty($auth_token) || empty($client_id)) {
            return null;
        }


        $result = '';
        return $result;
    }
}
