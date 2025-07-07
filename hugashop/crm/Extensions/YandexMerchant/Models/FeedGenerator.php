<?php

/**
 *
 * @author Andi Huga
 * @version 3.4
 *
 * Use Cache
 * Яндекс фид YXM
 *
 * date_default_timezone_set('Europe/Moscow'); # Устанавливается в config
 * @link https://yandex.ru/support/merchants/ru/offers
 *
 */

namespace HugaShop\Extensions\YandexMerchant\Models;

use HugaShop\Models\Image;
use HugaShop\Services\Cache;
use HugaShop\Models\Settings;
use HugaShop\Services\Config;
use HugaShop\Models\Product\Product;
use HugaShop\Models\Product\ProductOption;
use Symfony\Contracts\Cache\ItemInterface;
use HugaShop\Models\Finance\FinanceCurrency;
use HugaShop\Models\Product\ProductCategory;
use HugaShop\Extensions\YandexMerchant\Models\YandexMerchantCategory;

class FeedGenerator
{

    private static $pricefeed;

    public static function getPriceFeed(object $pricefeed)
    {

        self::$pricefeed = $pricefeed; # for use in cache function

        // The callable will only be executed on a cache miss.
        $response = Cache::cache(self::class)->get('item_' . $pricefeed->id, function (ItemInterface $item): string {
            $item->expiresAfter(10); # seconds

            // Заголовок
            $resp_xml =
                '<?xml version="1.0" encoding="UTF-8"?>
                <yml_catalog date="' . date(DATE_RFC3339) . '">
                    <shop>
                        <name>' . Settings::getParam('domain') . '</name>
                        <company>' . Settings::getParam('company_name') . '</company>
                        <url>' . Config::get('root_url') . '</url>
                        <delivery>true</delivery>';

            // Валюты
            $main_currency = FinanceCurrency::getMainCurrency();
            if (!empty(self::$pricefeed->currency_code)) { # RUR
                $main_currency->code = self::$pricefeed->currency_code;
            }

            $resp_xml .=  "<currencies>";
            $resp_xml .=  '<currency id="' . $main_currency->code . '" rate="1"/>';
            $resp_xml .=  "</currencies>";


            // Категории
            $categories = ProductCategory::getCategories();

            $resp_xml .=  "<categories>";

            foreach ($categories as $c) {
                $resp_xml .=  '<category id="' . $c->id . '"';
                if ($c->parent_id > 0) {
                    $resp_xml .=  ' parentId="' . $c->parent_id . '"';
                }
                $resp_xml .=  '>' . htmlspecialchars($c->name) . '</category>';
            }
            $resp_xml .=  '</categories>';


            // Товары
            $categories = YandexMerchantCategory::getCategoriesIds(self::$pricefeed->id);

            $filter['category_id'] = $categories;
            $filter['visible'] = 1;
            $raw_products = Product::getList($filter, 'position', ['image', 'brand']);

            $resp_xml .=  "<offers>";
            foreach ($raw_products as $raw_product) {

                // Нe показываем выключеные и не активные
                if (!empty($raw_product->disable) || empty($raw_product->visible)) {
                    continue;
                }

                if (empty($raw_product->name) || empty($raw_product->price)) {
                    continue;
                }

                // Не показываеам "нет в наличии"
                if ($raw_product->stock == 0  and empty(self::$pricefeed->show_out_stock)) {
                    continue;
                }


                // Товар доступен
                $available = 'false';
                if (!empty($raw_product->stock) and intval($raw_product->stock) > 0) {
                    $available = 'true';
                }

                // ID
                if (empty(self::$pricefeed->sku_id) || empty($raw_product->sku)) {
                    $item_id = $raw_product->id;
                } else {
                    $item_id = $raw_product->sku;
                }

                $resp_xml .= '<offer id="' . $item_id . '" available="' . $available . '">';

                // <name> - Оптимальная длина — 50‑60 символов, максимальная — 150.
                $name = htmlspecialchars(mb_substr($raw_product->name . ($raw_product->variant_name ? ' ' . $raw_product->variant_name : ''), 0, 150));
                $resp_xml .= '<name>' . $name . '</name>';

                $resp_xml .= '<url>' . Config::get('root_url') . '/tovar-' . $raw_product->url . '</url>';
                $resp_xml .= '<currencyId>' . $main_currency->code . '</currencyId>';
                $resp_xml .= '<categoryId>' . $raw_product->category_id . '</categoryId>';


                // Максимальная длина — 3000 знаков.
                // Лучше уложиться в 400–800 знаков — чтобы текст не выглядел чересчур объемным и сложным для восприятия.
                if (!empty($raw_product->annotation)) {
                    $resp_xml .= '<description>' . htmlspecialchars(strip_tags($raw_product->annotation)) . '</description>';
                }


                // Бренд
                if (!empty($raw_product->brand->name)) {
                    $resp_xml .= '<vendor>' . htmlspecialchars($raw_product->brand->name) . '</vendor>';
                }


                // Price
                $price = round(FinanceCurrency::priceConvert($raw_product->price, $main_currency->id, false), 2);
                $old_price = round(FinanceCurrency::priceConvert($raw_product->old_price, $main_currency->id, false), 2);

                $resp_xml .= '<price>' . $price . '</price>';
                if (!empty($raw_product->old_price) and $raw_product->price < $raw_product->old_price) {
                    $resp_xml .=  "<oldprice>" . $old_price . "</oldprice>";
                }


                // Options
                // Example: <param name="Размер экрана" unit="дюйм">27</param>
                $product_options = ProductOption::getProductOptions($raw_product->id);
                foreach ($product_options as $option) {
                    $resp_xml .= '<param name="' . htmlspecialchars($option->name) . '">' . htmlspecialchars($option->value) . '</param>';
                }


                // Добавляйте не больше 20 фотографий для одного товара.
                // На фотографиях может использоваться водяной знак и иная информация о товаре
                $images = Image::getImages($raw_product->id, 'product', true);
                if ($images->isNotEmpty()) {
                    foreach ($images as $img) {
                        $resp_xml .= '<picture>' . Image::getImageURL($img->filename, 1080, 1080, 'w') . '</picture>';
                    }
                }

                $resp_xml .= '</offer>';
            }

            $resp_xml .= '</offers>';
            $resp_xml .= '</shop>';
            $resp_xml .= '</yml_catalog>';


            return $resp_xml;
        });

        return $response;
    }
}
