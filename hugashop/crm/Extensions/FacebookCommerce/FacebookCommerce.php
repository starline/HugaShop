<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.3
 *
 */

namespace HugaShop\Extensions\FacebookCommerce;

use HugaShop\Models\Design;
use HugaShop\Models\Helper;
use HugaShop\Models\Request;
use HugaShop\Extensions\BaseExtension;
use HugaShop\Models\Product\ProductCategory;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use HugaShop\Extensions\FacebookCommerce\Models\FeedGenerator;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use HugaShop\Extensions\FacebookCommerce\Models\FacebookCommerceCategory;
use HugaShop\Extensions\FacebookCommerce\Models\FacebookCommerce as FacebookCommerceModel;

final class FacebookCommerce extends BaseExtension
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
                            FacebookCommerceModel::deleteOne($ids);
                            break;
                        }
                }
            }

            foreach (Helper::getPositions() as $id => $position) {
                FacebookCommerceModel::updateOne($id, ['position' => $position]);
            }

            Helper::cache(FeedGenerator::class)->clear();
        }

        $pricefeeds = FacebookCommerceModel::getList(order: 'position');
        Design::assign('pricefeeds', $pricefeeds);

        return $this->getTemplatePath('templates/feed_list.tpl');
    }


    /**
     * Pricelist
     * @param ?int $pricefeed_id
     */
    public function feed(?int $pricefeed_id = null)
    {

        $pricefeed_categories = [];

        #### Update
        ###########
        if (!empty($pricefeed = Request::getDataAcces(FacebookCommerceModel::getFields()))) {
            if (empty($pricefeed->id)) {
                $pricefeed->token = Helper::makeToken();
                $pricefeed = Design::setFlashMessage('add', FacebookCommerceModel::createOne($pricefeed));
            } else {
                Design::setFlashMessage('update', FacebookCommerceModel::updateOne($pricefeed->id, $pricefeed) >= 0);

                // Cache clean
                Helper::cache(FeedGenerator::class)->delete('item_' . $pricefeed->id);
            }

            $pricefeed_categories = Request::post('pricefeed_categories', 'array');
            FacebookCommerceCategory::setCategories($pricefeed->id, $pricefeed_categories);

            // Делаем редирект на страницу с ID
            Request::makeRedirect("/admin/extension/$this->class_name/feed/$pricefeed->id");
        }


        #### View
        #########
        if (!empty($pricefeed_id)) {

            $pricefeed = FacebookCommerceModel::getOne($pricefeed_id);

            if (empty($pricefeed->id)) {
                Request::makeRedirect("/admin/extension/$this->class_name");
            }

            $pricefeed_categories = FacebookCommerceCategory::getCategoriesIds($pricefeed->id);
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

        // Get token cut '.csv'
        // Example: a2e8e4cbf284cb268e2b4328eb66cd5e.csv
        $token = str_replace('.csv', '', $params['token']);

        if (!empty($pricefeed = FacebookCommerceModel::getOne(['id' => $params['id'], 'token' => $token]))) {
            $feed_data = FeedGenerator::getPriceFeed($pricefeed);

            // Encoding contents in CSV format
            $serializer = new Serializer([new ObjectNormalizer()], [new CsvEncoder()]);
            $csv = $serializer->encode($feed_data, 'csv');

            $response = new Response($csv);
            $response->headers->set('Content-type', 'text/csv');
            return $response;
        }

        return false;
    }
}
