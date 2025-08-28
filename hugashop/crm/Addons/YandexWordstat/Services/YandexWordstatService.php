<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.5
 * 
 * @link https://yandex.com/support2/wordstat/ru/content/api-wordstat
 * @link https://yandex.com/support2/wordstat/ru/content/api-structure
 * 
 * Получение токена для доступа к API:
 * @link https://yandex.ru/dev/id/doc/ru/tokens/debug-token
 * 
 * Regions list:
 * 
 * 255      - Россия
 * 10174    - Петербург и Ленинградская область
 * 1        - Москва и Московская область
 * 2        - Санкт-Петербург
 * 
 */

namespace HugaShop\Addons\YandexWordstat\Services;

use GuzzleHttp\Client as GuzzleClient;
use HugaShop\Addons\BaseAddonTrait;

class YandexWordstatService
{

    use BaseAddonTrait;

    private static $api_url = 'https://api.wordstat.yandex.net/v1/';

    /**
     * Получаем ключевые слова из Yandex Wordstat
     *
     * @param string $keyword
     * @param int $region
     */
    public static function getTopRequests(string $keyword, int $region = 225, int $limit = 50)
    {
        if (empty($client = self::wordstatClient())) {
            return null;
        }

        $params = [
            'phrase'        => $keyword,
            'numPhrases'    => $limit
        ];

        if (!empty($region)) {
            $params['regions'] = [$region];
        }

        $response = $client->request('POST', 'topRequests', [
            'json' => $params
        ]);

        return json_decode($response->getBody(), true);
    }


    /**
     * Получаем список регионов из Yandex Wordstat
     */
    public static function getRegions()
    {
        if (empty($client = self::wordstatClient())) {
            return null;
        }
        $response = $client->request('POST', 'getRegionsTree');
        return json_decode($response->getBody(), true);
    }


    /**
     * Create Guzzle client for Yandex Wordstat API
     */
    private static function wordstatClient()
    {
        $auth_token = self::getSettings()?->auth_token;
        $client_id  = self::getSettings()?->client_id;

        if (empty($auth_token) || empty($client_id)) {
            return null;
        }

        $client = new GuzzleClient([
            'base_uri' => self::$api_url,
            'headers' => [
                'Authorization'    => 'Bearer ' . $auth_token,
                'Client-Login'     => $client_id,
                'Accept-Language'  => 'ru',
                'Content-Type'     => 'application/json',
            ]
        ]);

        return $client;
    }
}
