<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.2
 */

namespace HugaShop\Extensions\GoogleMerchant\Controller;

use HugaShop\Services\Cache;
use HugaShop\Services\Design;
use HugaShop\Services\Helper;
use HugaShop\Services\Request;
use App\Controller\BaseAdminController;
use HugaShop\Extensions\BaseExtensionTrait;
use Symfony\Component\Routing\Attribute\Route;
use HugaShop\Extensions\GoogleMerchant\Services\FeedGenerator;
use HugaShop\Extensions\GoogleMerchant\Models\GoogleMerchant;

final class GoogleMerchantListController extends BaseAdminController
{
    use BaseExtensionTrait;

    #[Route('/GoogleMerchant', name: 'ExtGoogleMerchantList', priority: 20)]
    public function index()
    {
        if (Request::checkCSRF()) {
            $ids = Request::post('check');
            if (is_array($ids)) {
                switch (Request::post('action')) {
                    case 'delete':
                        foreach ($ids as $id) {
                            GoogleMerchant::deleteOne($id);
                        }
                        break;
                }
            }

            foreach (Helper::getPositions() as $id => $position) {
                GoogleMerchant::updateOne($id, ['position' => $position]);
            }

            Cache::cache(FeedGenerator::class)->clear();
        }

        Design::assign('pricefeeds', GoogleMerchant::getList(order: 'position'));
        Design::assign('extension', $this->getExtension());

        return $this->fetchExtResponse('feed_list.tpl');
    }
}
