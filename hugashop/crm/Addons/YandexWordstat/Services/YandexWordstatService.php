<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.2
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
     * Получаем ключевые слова из Yandex Wordstat
     *
     * @param string $keyword
     * @param string $language
     * @param int $region
     * @return array|null
     */
    public static function getKewords($keyword, $language = 'ru', $region = 225)
    {
        $auth_token = self::getSettings()?->auth_token;
        $client_id  = self::getSettings()?->client_id;

        if (empty($auth_token) || empty($client_id)) {
            return null;
        }

        $params = [
            'method' => 'Get',
            'params' => [
                'SelectionCriteria' => [
                    'Keywords' => [$keyword],
                    'GeoId'    => [$region],
                    'Language' => $language,
                ],
                'FieldNames' => [
                    'Keyword',
                    'Shows',
                ],
            ],
        ];

        $client = new \GuzzleHttp\Client([
            'base_uri' => 'https://api.direct.yandex.com/json/v5/',
        ]);

        try {
            $response = $client->request('POST', 'keywordsstat', [
                'headers' => [
                    'Authorization'    => 'Bearer ' . $auth_token,
                    'Client-Login'     => $client_id,
                    'Accept-Language'  => $language,
                    'Content-Type'     => 'application/json; charset=utf-8',
                ],
                'json' => $params,
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            return $data['result']['KeywordsStatItems'] ?? [];
        } catch (\Throwable $e) {
            return null;
        }
    }
}
