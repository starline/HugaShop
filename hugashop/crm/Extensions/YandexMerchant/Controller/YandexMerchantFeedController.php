<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.1
 * 
 */

namespace HugaShop\Extensions\YandexMerchant\Controller;

use App\Controller\BaseAdminController;
use HugaShop\Extensions\BaseExtensionTrait;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use HugaShop\Extensions\YandexMerchant\Models\YandexMerchant;
use HugaShop\Extensions\YandexMerchant\Services\FeedGenerator;

final class YandexMerchantFeedController extends BaseAdminController
{
    use BaseExtensionTrait;

    // Example: a2e8e4cbf284cb268e2b4328eb66cd5e.xml
    #[Route('/YandexMerchant/feed/{id}/{token}.xml', name: 'ExtYandexMerchantFeed', priority: 20)]
    public function feed(int $id, string $token)
    {
        if (empty($pricefeed = YandexMerchant::getOne(['id' => $id, 'token' => $token]))) {
            throw $this->createNotFoundException('Something is going wrong.'); # 404
        }

        $response = new Response(FeedGenerator::getPriceFeed($pricefeed));
        $response->headers->set('Content-type', 'text/xml');
        return $response;
    }
}
