<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.6
 */

namespace HugaShop\Extensions\FacebookCommerce\Controller;

use HugaShop\Services\Cache;
use HugaShop\Services\Design;
use HugaShop\Services\Helper;
use HugaShop\Services\Request;
use App\Controller\BaseAdminController;
use HugaShop\Extensions\BaseExtensionTrait;
use Symfony\Component\Routing\Attribute\Route;
use HugaShop\Extensions\FacebookCommerce\Models\FacebookCommerce as FacebookCommerceModel;
use HugaShop\Extensions\FacebookCommerce\Models\FeedGenerator;

final class FacebookCommerceListController extends BaseAdminController
{
    use BaseExtensionTrait;

    #[Route('/FacebookCommerce', name: 'ExtFacebookCommerceList', priority: 20)]
    public function index()
    {
        if (Request::checkCSRF()) {
            $ids = Request::post('check');
            if (is_array($ids)) {
                switch (Request::post('action')) {
                    case 'delete':
                        FacebookCommerceModel::deleteOne($ids);
                        break;
                }
            }

            foreach (Helper::getPositions() as $id => $position) {
                FacebookCommerceModel::updateOne($id, ['position' => $position]);
            }

            Cache::cache(FeedGenerator::class)->clear();
        }

        $pricefeeds = FacebookCommerceModel::getList(order: 'position');
        Design::assign('pricefeeds', $pricefeeds);

        return $this->fetchExtResponse('feed_list.tpl');
    }
}
