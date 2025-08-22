<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.2
 */

namespace HugaShop\Addons\YandexMerchant\Controller;

use HugaShop\Services\Cache;
use HugaShop\Services\Design;
use HugaShop\Services\Helper;
use HugaShop\Services\Secure;
use HugaShop\Services\Request;
use App\Controller\BaseAdminController;
use HugaShop\Addons\BaseAddonTrait;
use Symfony\Component\Routing\Attribute\Route;
use HugaShop\Addons\YandexMerchant\Models\YandexMerchant;
use HugaShop\Addons\YandexMerchant\Services\FeedGenerator;

final class YandexMerchantListController extends BaseAdminController
{
    use BaseAddonTrait;

    #[Route('/YandexMerchant', name: 'AddonYandexMerchantList', priority: 20)]
    public function index()
    {
        if (Secure::checkCSRF()) {
            $ids = Request::post('check');
            if (is_array($ids)) {
                switch (Request::post('action')) {
                    case 'delete':
                        foreach ($ids as $id) {
                            YandexMerchant::deleteOne($id);
                        }
                        break;
                }
            }

            foreach (Helper::getPositions() as $id => $position) {
                YandexMerchant::updateOne($id, ['position' => $position]);
            }

            Cache::cache(FeedGenerator::class)->clear();
        }

        Design::assign('pricefeeds', YandexMerchant::getList(order: 'position'));
        Design::assign('addon', $this->getAddon());

        return $this->fetchAddonResponse('feed_list.tpl');
    }
}
