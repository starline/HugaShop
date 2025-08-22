<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.3
 * 
 */

namespace HugaShop\Addons\GoogleMerchant\Controller;

use HugaShop\Services\Design;
use App\Controller\BaseFrontController;
use HugaShop\Addons\BaseAddonTrait;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use HugaShop\Addons\GoogleMerchant\Models\GoogleMerchant;
use HugaShop\Addons\GoogleMerchant\Services\FeedGenerator;

final class GoogleMerchantFeedController extends BaseFrontController
{

    use BaseAddonTrait;

    // Example: a2e8e4cbf284cb268e2b4328eb66cd5e.xml
    #[Route('/GoogleMerchant/feed/{id}/{token}.xml', name: 'ExtGoogleMerchantFeed', priority: 20)]
    public function feed(int $id, string $token)
    {

        if (empty($pricefeed = GoogleMerchant::getOne(['id' => $id, 'token' => $token]))) {
            throw $this->createNotFoundException('Something is going wrong.'); # 404
        }

        Design::assign('products', FeedGenerator::getPriceFeed($pricefeed));
        $response = new Response(Design::fetch($this->getTemplatePath('feed_generator.tpl')));
        $response->headers->set('Content-type', 'text/xml');
        return $response;
    }
}
