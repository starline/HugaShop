<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.8
 */

namespace HugaShop\Addons\FacebookCommerce\Controller;

use App\Controller\BaseFrontController;
use HugaShop\Addons\BaseAddonTrait;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use HugaShop\Addons\FacebookCommerce\Services\FeedGenerator;
use HugaShop\Addons\FacebookCommerce\Models\FacebookCommerce;

final class FacebookCommerceFeedController extends BaseFrontController
{

    use BaseAddonTrait;

    // Example: a2e8e4cbf284cb268e2b4328eb66cd5e.csv
    #[Route('/FacebookCommerce/feed/{id}/{token}.csv', name: 'ExtFacebookCommerceFeedCsv', priority: 20)]
    public function feed(int $id, string $token)
    {

        if (empty($pricefeed = FacebookCommerce::getOne(['id' => $id, 'token' => $token]))) {
            throw $this->createNotFoundException('Something is going wrong.'); # 404
        }

        $feed_data = FeedGenerator::getPriceFeed($pricefeed);

        // Encoding contents in CSV format
        $serializer = new Serializer([new ObjectNormalizer()], [new CsvEncoder()]);
        $csv = $serializer->encode($feed_data, 'csv');

        $response = new Response($csv);
        $response->headers->set('Content-type', 'text/csv');
        return $response;
    }
}
