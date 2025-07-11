<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.0
 *
 */

namespace HugaShop\Extensions\GoogleMerchant;

use HugaShop\Services\Cache;
use HugaShop\Services\Design;
use HugaShop\Services\Helper;
use HugaShop\Services\Request;
use HugaShop\Extensions\BaseExtension;
use HugaShop\Models\Product\ProductCategory;
use Symfony\Component\HttpFoundation\Response;
use HugaShop\Extensions\GoogleMerchant\Models\FeedGenerator;
use HugaShop\Extensions\GoogleMerchant\Models\GoogleMerchantCategory;
use HugaShop\Extensions\GoogleMerchant\Models\GoogleMerchant as GoogleMerchantModel;

final class GoogleMerchant extends BaseExtension
{

    /**
     * Список
     */
    public function index()
    {

        // Обработка действий
        if (Request::checkCSRF()) {

            // Действия с выбранными
            $ids = Request::post('check');
            if (is_array($ids)) {
                switch (Request::post('action')) {
                    case 'delete': {
                            foreach ($ids as $id) {
                                GoogleMerchantModel::deleteOne($id);
                            }
                            break;
                        }
                }
            }

            foreach (Helper::getPositions() as $id => $position) {
                GoogleMerchantModel::updateOne($id, ['position' => $position]);
            }

            Cache::cache(FeedGenerator::class)->clear();
        }

        $pricefeeds = GoogleMerchantModel::getList(order: 'position');
        Design::assign('pricefeeds', $pricefeeds);

        return $this->getTemplatePath('templates/feed_list.tpl');
    }


    /**
     * Pricelist
     * @param int $pricefeed_id
     */
    public function feed(?int $pricefeed_id = null)
    {

        $pricefeed_categories = [];

        #### Update
        ###########
        if (!empty($pricefeed = Request::getDataAcces(GoogleMerchantModel::getFields()))) {
            if (empty($pricefeed->id)) {
                $pricefeed->token = Helper::makeToken();
                $pricefeed = Design::setFlashMessage('add', GoogleMerchantModel::createOne($pricefeed));
            } else {
                Design::setFlashMessage('update', GoogleMerchantModel::updateOne($pricefeed->id, $pricefeed) >= 0);

                // Cache clean
                Cache::cache(FeedGenerator::class)->delete('item_' . $pricefeed->id);
            }

            $pricefeed_categories = Request::post('pricefeed_categories', 'array');
            GoogleMerchantCategory::setCategories($pricefeed->id, $pricefeed_categories);

            // Делаем редирект на страницу с ID
            Request::makeRedirect("/admin/extension/" . self::getName() . "/feed/$pricefeed->id");
        }


        #### View
        #########
        if (!empty($pricefeed_id)) {

            $pricefeed = GoogleMerchantModel::getOne($pricefeed_id);

            if (empty($pricefeed->id)) {
                Request::makeRedirect("/admin/extension/" . self::getName());
            }

            $pricefeed_categories = GoogleMerchantCategory::getCategoriesIds($pricefeed->id);
        }

        $categories = ProductCategory::getCategoriesTree();

        Design::assign('pricefeed', $pricefeed);
        Design::assign('categories', $categories);
        Design::assign('pricefeed_categories', $pricefeed_categories);

        // Проверим сущестование файла
        return $this->getTemplatePath('templates/feed.tpl');
    }


    /**
     * Webhook module
     * @param array $params
     */
    public function webhook(array $params = [])
    {
        if (empty($params['token']) || empty($params['id'])) {
            return false;
        }

        if (!empty($pricefeed = GoogleMerchantModel::getOne(['id' => $params['id'], 'token' => $params['token']]))) {

            Design::assign('products', FeedGenerator::getPriceFeed($pricefeed));

            $response = new Response(self::fetchTemplate('templates/feed_generator.tpl'));
            $response->headers->set('Content-type', 'text/xml');
            return $response;
        }

        return false;
    }
}
