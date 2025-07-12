<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.1
 *
 */

namespace HugaShop\Extensions\GoogleMerchant;

use HugaShop\Services\Design;
use HugaShop\Extensions\BaseExtension;
use Symfony\Component\HttpFoundation\Response;

use HugaShop\Extensions\GoogleMerchant\Services\FeedGenerator;
use HugaShop\Extensions\GoogleMerchant\Models\GoogleMerchant as GoogleMerchantModel;

final class GoogleMerchant extends BaseExtension
{


    /**
     * Webhook module
     * @param array $params
     */
    public function webhook(array $params = [])
    {
        if (empty($params['token']) || empty($params['id'])) {
            return false;
        }

        if (!empty($pricefeed = GoogleMerchantModel::getOne(['id' => $params['id'], 'token' => $params['token']]))) {

            Design::assign('products', FeedGenerator::getPriceFeed($pricefeed));

            $response = new Response(self::fetchTemplate('templates/feed_generator.tpl'));
            $response->headers->set('Content-type', 'text/xml');
            return $response;
        }

        return false;
    }
}
