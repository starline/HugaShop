<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.0
 *
 */

namespace HugaShop\Extensions\YandexMerchant;

use HugaShop\Extensions\BaseExtension;
use Symfony\Component\HttpFoundation\Response;
use HugaShop\Extensions\YandexMerchant\Models\FeedGenerator;
use HugaShop\Extensions\YandexMerchant\Models\YandexMerchant as YandexMerchantModel;

final class YandexMerchant extends BaseExtension
{

    /**
     * Webhook module
     */
    public function webhook(array $params = [])
    {
        if (empty($params['token']) || empty($params['id'])) {
            return false;
        }

        $pricefeed = YandexMerchantModel::getOne(['id' => $params['id'], 'token' => $params['token']]);
        $response = new Response(FeedGenerator::getPriceFeed($pricefeed));
        $response->headers->set('Content-type', 'text/xml');
        return $response;
    }
}
