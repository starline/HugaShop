<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.7
 *
 */

namespace HugaShop\Extensions\YandexMerchant;

use HugaShop\Services\Design;
use HugaShop\Models\Helper;
use HugaShop\Models\Request;
use HugaShop\Models\Product\ProductCategory;
use HugaShop\Extensions\BaseExtension;
use Symfony\Component\HttpFoundation\Response;
use HugaShop\Extensions\YandexMerchant\Models\YandexMerchant as YandexMerchantModel;
use HugaShop\Extensions\YandexMerchant\Models\YandexMerchantCategory;
use HugaShop\Extensions\YandexMerchant\Models\FeedGenerator;

final class YandexMerchant extends BaseExtension
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
                                YandexMerchantModel::deleteOne($id);
                            }
                            break;
                        }
                }
            }

            foreach (Helper::getPositions() as $id => $position) {
                YandexMerchantModel::updateOne($id, ['position' => $position]);
            }

            Helper::cache(FeedGenerator::class)->clear();
        }

        $pricefeeds = YandexMerchantModel::getList([], 'position');
        Design::assign('pricefeeds', $pricefeeds);

        return $this->getTemplatePath('templates/feed_list.tpl');
    }


    /**
     * PriceFeed
     * @param int $pricefeed_id
     */
    public function feed(?int $pricefeed_id = null)
    {


        #### Update
        ###########
        if (!empty($pricefeed = Request::getDataAcces(YandexMerchantModel::getFields()))) {

            if (empty($pricefeed->id)) {
                $pricefeed->token = Helper::makeToken();
                $pricefeed = Design::setFlashMessage('add', YandexMerchantModel::createOne($pricefeed));
            } else {
                Design::setFlashMessage('update', YandexMerchantModel::updateOne($pricefeed->id, $pricefeed) >= 0);

                // Cache clean
                Helper::cache(FeedGenerator::class)->delete('item_' . $pricefeed->id);
            }

            $category_ids = Request::post('pricefeed_categories', 'array');
            YandexMerchantCategory::setCategories($pricefeed->id, $category_ids);

            // Делаем редирект на страницу с ID
            Request::makeRedirect("/admin/extension/YandexMerchant/feed/$pricefeed->id");
        }


        #### View
        #########
        if (!empty($pricefeed_id)) {

            $pricefeed = YandexMerchantModel::getOne($pricefeed_id);

            if (empty($pricefeed->id)) {
                Request::makeRedirect("/admin/extension/YandexMerchant");
            }

            $category_ids = YandexMerchantCategory::getCategoriesIds($pricefeed->id);
            Design::assign('pricefeed_categories', $category_ids);
        }

        $categories = ProductCategory::getCategoriesTree();

        Design::assign('pricefeed', $pricefeed);
        Design::assign('categories', $categories);

        // Проверим сущестование файла
        return $this->getTemplatePath('templates/feed.tpl');
    }


    /**
     * Webhook module
     */
    public function webhook(array $params = [])
    {
        if (empty($params['token']) || empty($params['id'])) {
            return false;
        }

        $pricefeed = YandexMerchantModel::getOne(['id' => $params['id'], 'token' => $params['token']]);
        $response = new Response(FeedGenerator::getPriceFeed($pricefeed));
        $response->headers->set('Content-type', 'text/xml');
        return $response;
    }
}
