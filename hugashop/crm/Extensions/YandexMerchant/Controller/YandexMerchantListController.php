<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.1
 */

namespace HugaShop\Extensions\YandexMerchant\Controller;

use HugaShop\Services\Cache;
use HugaShop\Services\Design;
use HugaShop\Services\Helper;
use HugaShop\Services\Request;
use App\Controller\BaseAdminController;
use HugaShop\Extensions\BaseExtensionTrait;
use Symfony\Component\Routing\Attribute\Route;
use HugaShop\Extensions\YandexMerchant\Models\YandexMerchant;
use HugaShop\Extensions\YandexMerchant\Services\FeedGenerator;

final class YandexMerchantListController extends BaseAdminController
{
    use BaseExtensionTrait;

    #[Route('/YandexMerchant', name: 'ExtYandexMerchantList', priority: 20)]
    public function index()
    {
        if (Request::checkCSRF()) {
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

        $pricefeeds = YandexMerchant::getList(order: 'position');

        Design::assign('pricefeeds', $pricefeeds);
        Design::assign('extension', $this->getExtension());

        return $this->fetchExtResponse('feed_list.tpl');
    }
}
