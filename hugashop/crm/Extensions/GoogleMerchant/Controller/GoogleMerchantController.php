<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.2
 * 
 */

namespace HugaShop\Extensions\GoogleMerchant\Controller;

use HugaShop\Services\Cache;
use HugaShop\Services\Design;
use HugaShop\Services\Helper;
use HugaShop\Services\Request;
use App\Controller\BaseAdminController;
use HugaShop\Extensions\BaseExtensionTrait;
use Symfony\Component\Routing\Attribute\Route;
use HugaShop\Models\Product\ProductCategory;
use HugaShop\Extensions\GoogleMerchant\Services\FeedGenerator;
use HugaShop\Extensions\GoogleMerchant\Models\GoogleMerchantCategory;
use HugaShop\Extensions\GoogleMerchant\Models\GoogleMerchant;

final class GoogleMerchantController extends BaseAdminController
{
    use BaseExtensionTrait;

    #[Route('/GoogleMerchant/feed', name: 'ExtGoogleMerchantNew', priority: 20)]
    #[Route('/GoogleMerchant/feed/{id}', name: 'ExtGoogleMerchant', priority: 20)]
    public function feed(?int $id = null)
    {
        $pricefeed_categories = [];

        if (!empty($pricefeed = Request::getInputCheckEditAccess(GoogleMerchant::class, $id))) {
            if (empty($pricefeed->id)) {
                $pricefeed->token = Helper::makeToken();
                $pricefeed = Design::setFlashMessage('add', GoogleMerchant::createOne($pricefeed));
            } else {
                Design::setFlashMessage('update', GoogleMerchant::updateOne($pricefeed->id, $pricefeed) >= 0);
                Cache::cache(FeedGenerator::class)->delete('item_' . $pricefeed->id);
            }

            $pricefeed_categories = Request::post('pricefeed_categories', 'array');
            GoogleMerchantCategory::setCategories($pricefeed->id, $pricefeed_categories);

            return $this->redirectToRoute('ExtGoogleMerchant', ['id' => $pricefeed->id]);
        }

        if (!empty($id)) {
            $pricefeed = GoogleMerchant::getOne($id);

            if (empty($pricefeed->id)) {
                return $this->redirectToRoute('ExtGoogleMerchantList');
            }

            $pricefeed_categories = GoogleMerchantCategory::getCategoriesIds($pricefeed->id);
        }

        $categories = ProductCategory::getCategoriesTree();

        Design::assign('pricefeed', $pricefeed);
        Design::assign('categories', $categories);
        Design::assign('pricefeed_categories', $pricefeed_categories);
        Design::assign('extension', $this->getExtension());

        return $this->fetchExtResponse('feed.tpl');
    }
}
