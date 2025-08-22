<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.7
 */

namespace HugaShop\Addons\FacebookCommerce\Controller;

use HugaShop\Services\Cache;
use HugaShop\Services\Design;
use HugaShop\Services\Helper;
use HugaShop\Services\Secure;
use HugaShop\Services\Request;
use App\Controller\BaseAdminController;
use HugaShop\Addons\BaseAddonTrait;
use Symfony\Component\Routing\Attribute\Route;
use HugaShop\Addons\FacebookCommerce\Services\FeedGenerator;
use HugaShop\Addons\FacebookCommerce\Models\FacebookCommerce;

final class FacebookCommerceListController extends BaseAdminController
{
    use BaseAddonTrait;

    #[Route('/FacebookCommerce', name: 'ExtFacebookCommerceList', priority: 20)]
    public function facebook()
    {
        if (Secure::checkCSRF()) {
            $ids = Request::post('check');
            if (is_array($ids)) {
                switch (Request::post('action')) {
                    case 'delete':
                        FacebookCommerce::deleteOne($ids);
                        break;
                }
            }

            foreach (Helper::getPositions() as $id => $position) {
                FacebookCommerce::updateOne($id, ['position' => $position]);
            }

            Cache::cache(FeedGenerator::class)->clear();
        }

        $pricefeeds = FacebookCommerce::getList(order: 'position');

        Design::assign('pricefeeds', $pricefeeds);
        Design::assign('addon', $this->getAddon());

        return $this->fetchAddonResponse('feed_list.tpl');
    }
}
