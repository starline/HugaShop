<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.7
 *
 */

namespace HugaShop\Extensions\FacebookCommerce;

use HugaShop\Extensions\BaseExtension;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use HugaShop\Extensions\FacebookCommerce\Services\FeedGenerator;
use HugaShop\Extensions\FacebookCommerce\Models\FacebookCommerce as FacebookCommerceModel;

final class FacebookCommerce extends BaseExtension
{
    /**
     * Webhook module
     * @param array $params
     */
    public static function webhook(array $params = [])
    {

        if (empty($params['token']) || empty($params['id'])) {
            return false;
        }

        // Get token cut '.csv'
        // Example: a2e8e4cbf284cb268e2b4328eb66cd5e.csv
        $token = str_replace('.csv', '', $params['token']);

        if (!empty($pricefeed = FacebookCommerceModel::getOne(['id' => $params['id'], 'token' => $token]))) {
            $feed_data = FeedGenerator::getPriceFeed($pricefeed);

            // Encoding contents in CSV format
            $serializer = new Serializer([new ObjectNormalizer()], [new CsvEncoder()]);
            $csv = $serializer->encode($feed_data, 'csv');

            $response = new Response($csv);
            $response->headers->set('Content-type', 'text/csv');
            return $response;
        }

        return false;
    }
}
