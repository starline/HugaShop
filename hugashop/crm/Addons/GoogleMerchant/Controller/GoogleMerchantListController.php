<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.3
 */

namespace HugaShop\Addons\GoogleMerchant\Controller;

use HugaShop\Services\Cache;
use HugaShop\Services\Design;
use HugaShop\Services\Helper;
use HugaShop\Services\Secure;
use HugaShop\Services\Request;
use App\Controller\BaseAdminController;
use HugaShop\Addons\BaseAddonTrait;
use Symfony\Component\Routing\Attribute\Route;
use HugaShop\Addons\GoogleMerchant\Models\GoogleMerchant;
use HugaShop\Addons\GoogleMerchant\Services\FeedGenerator;

final class GoogleMerchantListController extends BaseAdminController
{
    use BaseAddonTrait;

    #[Route('/GoogleMerchant', name: 'AddonGoogleMerchantList', priority: 20)]
    public function index()
    {
        if (Secure::checkCSRF()) {
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
        Design::assign('addon', $this->getAddon());

        return $this->fetchAddonResponse('feed_list.tpl');
    }
}
