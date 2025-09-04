<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.3
 * 
 */

namespace HugaShop\Addons\GoogleMerchant\Controller;

use HugaShop\Services\Cache;
use HugaShop\Services\Design;
use HugaShop\Services\Helper;
use HugaShop\Services\Secure;
use HugaShop\Services\Request;
use HugaShop\Addons\BaseAddonTrait;
use HugaShop\Models\Product\Product;
use App\Controller\BaseAdminController;
use HugaShop\Models\Product\ProductCategory;
use Symfony\Component\Routing\Attribute\Route;
use HugaShop\Addons\GoogleMerchant\Models\GoogleMerchant;
use HugaShop\Addons\GoogleMerchant\Services\FeedGenerator;
use HugaShop\Addons\GoogleMerchant\Models\GoogleMerchantCategory;

final class GoogleMerchantController extends BaseAdminController
{
    use BaseAddonTrait;

    #[Route('/GoogleMerchant/feed', name: 'AddonGoogleMerchantNew', priority: 20)]
    #[Route('/GoogleMerchant/feed/{id}', name: 'AddonGoogleMerchant', priority: 20)]
    public function feed(?int $id = null)
    {
        if (!empty($pricefeed = Secure::getInputCheckEditAccess(GoogleMerchant::class, $id))) {
            if (empty($pricefeed->id)) {
                $pricefeed->token = Helper::makeToken();
                $pricefeed = Design::setFlashMessage('add', GoogleMerchant::createOne($pricefeed));
            } else {
                Design::setFlashMessage('update', GoogleMerchant::updateOne($pricefeed->id, $pricefeed));
                Cache::cache(FeedGenerator::class)->delete('item_' . $pricefeed->id);
            }

            $pricefeed_categories = Request::post('pricefeed_categories', 'array');
            GoogleMerchantCategory::setCategories($pricefeed->id, $pricefeed_categories);

            return $this->redirectToRoute('AddonGoogleMerchant', ['id' => $pricefeed->id]);
        }

        if (!empty($id)) {
            $pricefeed = GoogleMerchant::getOne($id);

            if (empty($pricefeed->id)) {
                return $this->redirectToRoute('AddonGoogleMerchantList');
            }

            $pricefeed_categories   = GoogleMerchantCategory::getCategoriesIds($pricefeed->id);
            $products_count         = Product::countProducts(FeedGenerator::getProductFilter($pricefeed));
        }

        Design::assign('pricefeed',             $pricefeed);
        Design::assign('categories',            ProductCategory::getCategoriesTree());
        Design::assign('pricefeed_categories',  $pricefeed_categories ?? []);
        Design::assign('products_count',        $products_count ?? 0);
        Design::assign('addon',                 $this->getAddon());

        return $this->fetchAddonResponse('feed.tpl');
    }
}
