<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.0
 */

namespace HugaShop\Extensions\GoogleMerchant\Controller;

use HugaShop\Services\Cache;
use HugaShop\Services\Design;
use HugaShop\Services\Helper;
use HugaShop\Services\Request;
use App\Controller\BaseAdminController;
use HugaShop\Extensions\BaseExtensionTrait;
use Symfony\Component\Routing\Attribute\Route;
use HugaShop\Extensions\GoogleMerchant\Models\FeedGenerator;
use HugaShop\Extensions\GoogleMerchant\Models\GoogleMerchant as GoogleMerchantModel;

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
                            GoogleMerchantModel::deleteOne($id);
                        }
                        break;
                }
            }

            foreach (Helper::getPositions() as $id => $position) {
                GoogleMerchantModel::updateOne($id, ['position' => $position]);
            }

            Cache::cache(FeedGenerator::class)->clear();
        }

        $pricefeeds = GoogleMerchantModel::getList(order: 'position');

        Design::assign('pricefeeds', $pricefeeds);
        Design::assign('extension', $this->getExtension());

        return $this->fetchExtResponse('feed_list.tpl');
    }
}
