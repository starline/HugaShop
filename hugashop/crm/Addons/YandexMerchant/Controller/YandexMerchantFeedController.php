<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.2
 * 
 */

namespace HugaShop\Addons\YandexMerchant\Controller;

use App\Controller\BaseAdminController;
use HugaShop\Addons\BaseAddonTrait;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use HugaShop\Addons\YandexMerchant\Models\YandexMerchant;
use HugaShop\Addons\YandexMerchant\Services\FeedGenerator;

final class YandexMerchantFeedController extends BaseAdminController
{
    use BaseAddonTrait;

    // Example: a2e8e4cbf284cb268e2b4328eb66cd5e.xml
    #[Route('/YandexMerchant/feed/{id}/{token}.xml', name: 'AddonYandexMerchantFeed', priority: 20)]
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
