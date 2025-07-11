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
use HugaShop\Models\Product\ProductCategory;
use HugaShop\Extensions\FacebookCommerce\Models\FacebookCommerce;
use HugaShop\Extensions\FacebookCommerce\Models\FacebookCommerceCategory;
use HugaShop\Extensions\FacebookCommerce\Models\FeedGenerator;

final class FacebookCommerceController extends BaseAdminController
{
    use BaseExtensionTrait;

    #[Route('/FacebookCommerce/feed', name: 'ExtFacebookCommerceFeedNew', priority: 20)]
    #[Route('/FacebookCommerce/feed/{id}', name: 'ExtFacebookCommerceFeed', priority: 20)]
    public function feed(?int $id = null)
    {
        $pricefeed_categories = [];

        #### Update
        ###########
        if (!empty($pricefeed = Request::getDataAcces(FacebookCommerce::getFields()))) {
            if (empty($pricefeed->id)) {
                $pricefeed->token = Helper::makeToken();
                $pricefeed = Design::setFlashMessage('add', FacebookCommerce::createOne($pricefeed));
            } else {
                Design::setFlashMessage('update', FacebookCommerce::updateOne($pricefeed->id, $pricefeed) >= 0);

                Cache::cache(FeedGenerator::class)->delete('item_' . $pricefeed->id);
            }

            $pricefeed_categories = Request::post('pricefeed_categories', 'array');
            FacebookCommerceCategory::setCategories($pricefeed->id, $pricefeed_categories);

            return $this->redirectToRoute('ExtFacebookCommerceFeed', ['id' => $pricefeed->id]);
        }

        #### View
        #########
        if (!empty($id)) {
            $pricefeed = FacebookCommerce::getOne($id);

            if (empty($pricefeed->id)) {
                return $this->redirectToRoute('ExtFacebookCommerceList');
            }

            $pricefeed_categories = FacebookCommerceCategory::getCategoriesIds($pricefeed->id);
        }

        $categories = ProductCategory::getCategoriesTree();

        Design::assign('pricefeed', $pricefeed);
        Design::assign('categories', $categories);
        Design::assign('pricefeed_categories', $pricefeed_categories);

        return $this->fetchExtResponse('feed.tpl');
    }
}
