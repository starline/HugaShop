<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.0
 */

namespace HugaShop\Extensions\YandexMerchant\Controller;

use HugaShop\Services\Cache;
use HugaShop\Services\Design;
use HugaShop\Services\Helper;
use HugaShop\Services\Request;
use App\Controller\BaseAdminController;
use HugaShop\Extensions\BaseExtensionTrait;
use Symfony\Component\Routing\Attribute\Route;
use HugaShop\Extensions\YandexMerchant\Models\FeedGenerator;
use HugaShop\Extensions\YandexMerchant\Models\YandexMerchant as YandexMerchantModel;

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
                            YandexMerchantModel::deleteOne($id);
                        }
                        break;
                }
            }

            foreach (Helper::getPositions() as $id => $position) {
                YandexMerchantModel::updateOne($id, ['position' => $position]);
            }

            Cache::cache(FeedGenerator::class)->clear();
        }

        $pricefeeds = YandexMerchantModel::getList(order: 'position');

        Design::assign('pricefeeds', $pricefeeds);
        Design::assign('extension', $this->getExtension());

        return $this->fetchExtResponse('feed_list.tpl');
    }
}
