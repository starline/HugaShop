<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.0
 *
 * Интеграция с TyreClub Segment API.
 * @link https://gate.opt.tyreclub.com.ua/OneBox/RequestPriceList
 */

namespace HugaShop\Addons\TyreClub\Services;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;
use HugaShop\Addons\BaseAddonTrait;
use HugaShop\Addons\TyreClub\Models\Offer as TyreClubOffer;
use HugaShop\Addons\TyreClub\Models\Product as TyreClubProduct;
use Illuminate\Database\Capsule\Manager as DB;

final class TyreClubService
{
    use BaseAddonTrait;

    private const API_BASE_URL = 'https://gate.opt.tyreclub.com.ua/OneBox/';

    public static function requestPriceList(?int $date_from = null, ?string $callback_url = null): ?array
    {
        $api_key = self::getApiKey();
        $client  = self::createClient();

        if (empty($client) || empty($api_key)) {
            return null;
        }

        $payload = array_filter([
            'key'          => $api_key,
            'date_from'    => $date_from,
            'callback_url' => $callback_url,
        ], static fn($value) => $value !== null && $value !== '');

        try {
            $response = $client->post('RequestPriceList', [
                'json'        => $payload,
                'timeout'     => 60,
                'http_errors' => false,
            ]);
        } catch (GuzzleException $exception) {
            return null;
        }

        $body = (string) $response->getBody();
        if ($body === '') {
            return null;
        }

        $data = json_decode($body, true);
        if (!is_array($data)) {
            return null;
        }

        self::persistApiResponse($client, $data);

        return $data;
    }


    private static function createClient(): ?GuzzleClient
    {
        return new GuzzleClient([
            'base_uri' => self::API_BASE_URL,
            'timeout'  => 60,
        ]);
    }


    private static function persistApiResponse(GuzzleClient $client, array $response): void
    {
        $products = self::extractProducts($response);

        $download_url = $response['download_url']
            ?? $response['downloadUrl']
            ?? $response['url']
            ?? null;

        if ($products === null && !empty($download_url)) {
            $products = self::downloadPriceList($client, (string) $download_url);
        }

        if (empty($products)) {
            return;
        }

        self::persistProducts($products);
    }


    private static function extractProducts(array $response): ?array
    {
        $candidates = [];

        foreach (['products', 'price_list'] as $key) {
            if (isset($response[$key]) && is_array($response[$key])) {
                $candidates[] = $response[$key];
            }
        }

        if (isset($response['data']) && is_array($response['data'])) {
            $data = $response['data'];
            if (isset($data['products']) && is_array($data['products'])) {
                $candidates[] = $data['products'];
            } elseif (array_is_list($data)) {
                $candidates[] = $data;
            }
        }

        if (array_is_list($response) && isset($response[0]) && is_array($response[0])) {
            $candidates[] = $response;
        }

        foreach ($candidates as $candidate) {
            if (array_is_list($candidate)) {
                return $candidate;
            }
        }

        return null;
    }


    private static function downloadPriceList(GuzzleClient $client, string $url): ?array
    {
        $download_url = str_contains($url, 'contentOutput=1')
            ? $url
            : $url . (str_contains($url, '?') ? '&' : '?') . 'contentOutput=1';

        try {
            $response = $client->get($download_url, [
                'timeout'     => 60,
                'http_errors' => false,
            ]);
        } catch (GuzzleException $exception) {
            return null;
        }

        $body = (string) $response->getBody();
        if ($body === '') {
            return null;
        }

        $data = json_decode($body, true);
        if (!is_array($data)) {
            return null;
        }

        return self::extractProducts($data);
    }


    private static function persistProducts(array $products): void
    {
        $product_model = TyreClubProduct::getModel();
        $offer_model   = TyreClubOffer::getModel();

        $product_model->runWithInitTable(static fn() => null);
        $offer_model->runWithInitTable(static fn() => null);

        DB::connection()->transaction(static function () use ($products) {
            foreach ($products as $product) {
                if (!is_array($product)) {
                    continue;
                }

                $product_payload = self::mapProductPayload($product);
                if (empty($product_payload['external_id'])) {
                    continue;
                }

                $product_entity = TyreClubProduct::query()->updateOrCreate(
                    ['external_id' => $product_payload['external_id']],
                    $product_payload
                );

                TyreClubOffer::query()->where('product_id', $product_entity->id)->delete();

                if (empty($product['offers']) || !is_array($product['offers'])) {
                    continue;
                }

                foreach ($product['offers'] as $offer) {
                    if (!is_array($offer)) {
                        continue;
                    }

                    $offer_payload = self::mapOfferPayload(
                        $offer,
                        $product_entity->id,
                        $product_payload['external_id']
                    );

                    TyreClubOffer::query()->create($offer_payload);
                }
            }
        });
    }


    private static function mapProductPayload(array $product): array
    {
        return [
            'external_id'     => self::normalizeInt($product['id'] ?? null),
            'model_id'        => self::normalizeInt($product['model_id'] ?? null),
            'brand_id'        => self::normalizeInt($product['brand_id'] ?? null),
            'full_name'       => self::normalizeString($product['full_name'] ?? null),
            'reinforce_id'    => self::normalizeInt($product['reinforce_id'] ?? null),
            'ply_rating'      => self::normalizeInt($product['ply_rating'] ?? null),
            'studded'         => !empty($product['studded']) ? 1 : 0,
            'seal'            => !empty($product['seal']) ? 1 : 0,
            'silent'          => !empty($product['silent']) ? 1 : 0,
            'width'           => self::normalizeInt($product['width'] ?? null),
            'height'          => self::normalizeInt($product['height'] ?? null),
            'diameter'        => self::normalizeInt($product['diameter'] ?? null),
            'load_index'      => self::normalizeInt($product['load_index'] ?? null),
            'speed_index'     => self::normalizeString($product['speed_index'] ?? null),
            'vehicle_type_id' => self::normalizeInt($product['vehicle_type_id'] ?? null),
            'photo_url'       => self::normalizeString($product['photo_url'] ?? null),
        ];
    }


    private static function mapOfferPayload(array $offer, int $product_id, int $external_id): array
    {
        return [
            'product_id'            => $product_id,
            'product_external_id'   => $external_id,
            'source_price_wholesale' => self::normalizeDecimal($offer['source_price_wholesale'] ?? null),
            'source_price_retail'   => self::normalizeDecimal($offer['source_price_retail'] ?? null),
            'user_wholesale_price'  => self::normalizeDecimal($offer['user_wholesale_price'] ?? null),
            'user_price_retail'     => self::normalizeDecimal($offer['user_price_retail'] ?? null),
            'provider_id'           => self::normalizeInt($offer['provider_id'] ?? null),
            'in_stock'              => self::normalizeInt($offer['in_stock'] ?? null),
            'country'               => self::normalizeInt($offer['country'] ?? null),
            'year'                  => self::normalizeInt($offer['year'] ?? null),
            'date'                  => self::normalizeInt($offer['date'] ?? null),
        ];
    }


    private static function normalizeInt(mixed $value): ?int
    {
        if (is_bool($value)) {
            return $value ? 1 : 0;
        }

        if (is_numeric($value)) {
            return (int) $value;
        }

        if (is_string($value)) {
            $value = trim($value);
            if ($value === '') {
                return null;
            }
            return is_numeric($value) ? (int) $value : null;
        }

        return null;
    }


    private static function normalizeDecimal(mixed $value): ?float
    {
        if (is_null($value)) {
            return null;
        }

        if (is_string($value)) {
            $value = trim(str_replace(',', '.', $value));
            if ($value === '') {
                return null;
            }
        }

        if (!is_numeric($value)) {
            return null;
        }

        return (float) $value;
    }


    private static function normalizeString(mixed $value): ?string
    {
        if (is_null($value)) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }


    private static function getApiKey(): ?string
    {
        $settings = self::getSettings();
        if (empty($settings) || !isset($settings->api_key)) {
            return null;
        }

        $api_key = trim((string) $settings->api_key);

        return $api_key !== '' ? $api_key : null;
    }
}
