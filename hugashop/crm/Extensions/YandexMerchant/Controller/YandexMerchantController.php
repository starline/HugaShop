<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.2
 */

namespace HugaShop\Extensions\YandexMerchant\Controller;

use HugaShop\Services\Cache;
use HugaShop\Services\Design;
use HugaShop\Services\Helper;
use HugaShop\Services\Request;
use App\Controller\BaseAdminController;
use HugaShop\Extensions\BaseExtensionTrait;
use HugaShop\Models\Product\ProductCategory;
use Symfony\Component\Routing\Attribute\Route;
use HugaShop\Extensions\YandexMerchant\Models\YandexMerchant;
use HugaShop\Extensions\YandexMerchant\Services\FeedGenerator;
use HugaShop\Extensions\YandexMerchant\Models\YandexMerchantCategory;

final class YandexMerchantController extends BaseAdminController
{
    use BaseExtensionTrait;

    #[Route('/YandexMerchant/feed', name: 'ExtYandexMerchantNew', priority: 20)]
    #[Route('/YandexMerchant/feed/{id}', name: 'ExtYandexMerchant', priority: 20)]
    public function feed(?int $id = null)
    {
        $pricefeed_categories = [];

        if (!empty($pricefeed = Request::getInputCheckEditAccess(YandexMerchant::class, $id))) {
            if (empty($pricefeed->id)) {
                $pricefeed->token = Helper::makeToken();
                $pricefeed = Design::setFlashMessage('add', YandexMerchant::createOne($pricefeed));
            } else {
                Design::setFlashMessage('update', YandexMerchant::updateOne($pricefeed->id, $pricefeed));
                Cache::cache(FeedGenerator::class)->delete('item_' . $pricefeed->id);
            }

            $pricefeed_categories = Request::post('pricefeed_categories', 'array');
            YandexMerchantCategory::setCategories($pricefeed->id, $pricefeed_categories);

            return $this->redirectToRoute('ExtYandexMerchant', ['id' => $pricefeed->id]);
        }

        if (!empty($id)) {
            $pricefeed = YandexMerchant::getOne($id);
            if (empty($pricefeed->id)) {
                return $this->redirectToRoute('ExtYandexMerchantList');
            }

            $pricefeed_categories = YandexMerchantCategory::getCategoriesIds($pricefeed->id);
        }

        Design::assign('pricefeed', $pricefeed);
        Design::assign('categories', ProductCategory::getCategoriesTree());
        Design::assign('pricefeed_categories', $pricefeed_categories);
        Design::assign('extension', $this->getExtension());

        return $this->fetchExtResponse('feed.tpl');
    }
}
