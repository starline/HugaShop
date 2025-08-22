<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.3
 */

namespace HugaShop\Addons\YandexMerchant\Controller;

use HugaShop\Services\Cache;
use HugaShop\Services\Design;
use HugaShop\Services\Helper;
use HugaShop\Services\Secure;
use HugaShop\Services\Request;
use App\Controller\BaseAdminController;
use HugaShop\Addons\BaseAddonTrait;
use HugaShop\Models\Product\ProductCategory;
use Symfony\Component\Routing\Attribute\Route;
use HugaShop\Addons\YandexMerchant\Models\YandexMerchant;
use HugaShop\Addons\YandexMerchant\Services\FeedGenerator;
use HugaShop\Addons\YandexMerchant\Models\YandexMerchantCategory;

final class YandexMerchantController extends BaseAdminController
{
    use BaseAddonTrait;

    #[Route('/YandexMerchant/feed', name: 'AddonYandexMerchantNew', priority: 20)]
    #[Route('/YandexMerchant/feed/{id}', name: 'AddonYandexMerchant', priority: 20)]
    public function feed(?int $id = null)
    {
        $pricefeed_categories = [];

        if (!empty($pricefeed = Secure::getInputCheckEditAccess(YandexMerchant::class, $id))) {
            if (empty($pricefeed->id)) {
                $pricefeed->token = Helper::makeToken();
                $pricefeed = Design::setFlashMessage('add', YandexMerchant::createOne($pricefeed));
            } else {
                Design::setFlashMessage('update', YandexMerchant::updateOne($pricefeed->id, $pricefeed));
                Cache::cache(FeedGenerator::class)->delete('item_' . $pricefeed->id);
            }

            $pricefeed_categories = Request::post('pricefeed_categories', 'array');
            YandexMerchantCategory::setCategories($pricefeed->id, $pricefeed_categories);

            return $this->redirectToRoute('AddonYandexMerchant', ['id' => $pricefeed->id]);
        }

        if (!empty($id)) {
            $pricefeed = YandexMerchant::getOne($id);
            if (empty($pricefeed->id)) {
                return $this->redirectToRoute('AddonYandexMerchantList');
            }

            $pricefeed_categories = YandexMerchantCategory::getCategoriesIds($pricefeed->id);
        }

        Design::assign('pricefeed', $pricefeed);
        Design::assign('categories', ProductCategory::getCategoriesTree());
        Design::assign('pricefeed_categories', $pricefeed_categories);
        Design::assign('addon', $this->getAddon());

        return $this->fetchAddonResponse('feed.tpl');
    }
}
