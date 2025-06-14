<?php

/**
 *
 * @author Andi Huga
 * @version 3.0
 *
 * Use Cache
 * Яндекс фид YXM
 *
 * date_default_timezone_set('Europe/Moscow'); # Устанавливается в config
 * @link https://yandex.ru/support/merchants/ru/offers
 *
 */

namespace HugaShop\Extensions\YandexMerchant\Model;

use HugaShop\Api\Image;
use HugaShop\Api\Config;
use HugaShop\Api\Helper;
use HugaShop\Api\Settings;
use HugaShop\Api\Product\ProductOption;
use HugaShop\Api\Product\ProductVariant;
use HugaShop\Api\Finance\FinanceCurrency;
use HugaShop\Api\Product\ProductCategory;
use Symfony\Contracts\Cache\ItemInterface;
use HugaShop\Extensions\YandexMerchant\Model\YandexMerchantCategory;

class FeedGenerator
{

    private static $pricefeed;

    public static function getPriceFeed(object $pricefeed)
    {

        self::$pricefeed = $pricefeed; # for use in cache function

        // The callable will only be executed on a cache miss.
        $response = Helper::cache(self::class)->get($pricefeed->id, function (ItemInterface $item): string {
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
            $product_variants = ProductVariant::getVariants($filter, ['product_id' => 'ASC', 'position' => 'ASC'], ['Product', 'Image', 'ProductBrand', 'ProductCategory']);

            $resp_xml .=  "<offers>";
            $prev_product_id = null;
            foreach ($product_variants as $p) {

                // Нe показываем выключеные и не активные
                if (!empty($p->disable) || empty($p->visible)) {
                    continue;
                }

                if (empty($p->product_name) || empty($p->price)) {
                    continue;
                }

                // Не показываеам "нет в наличии"
                if ($p->stock == 0  and empty($pricefeed->show_out_stock)) {
                    continue;
                }


                $variant_url = '';
                if ($prev_product_id === $p->product_id) {
                    $variant_url = '?variant=' . $p->variant_id;
                }
                $prev_product_id = $p->product_id;


                // Товар доступен
                $available = 'false';
                if (!empty($p->stock) and intval($p->stock) > 0) {
                    $available = 'true';
                }

                // ID
                if (empty($pricefeed->sku_id) || empty($p->sku)) {
                    $item_id = $p->variant_id;
                } else {
                    $item_id = $p->sku;
                }

                $resp_xml .= '<offer id="' . $item_id . '" available="' . $available . '">';

                // <name> - Оптимальная длина — 50‑60 символов, максимальная — 150.
                $name = htmlspecialchars(mb_substr($p->product_name . ($p->variant_name ? ' ' . $p->variant_name : ''), 0, 150));
                $resp_xml .= '<name>' . $name . '</name>';

                $resp_xml .= '<url>' . Config::get('root_url') . '/tovar-' . $p->url . $variant_url . '</url>';
                $resp_xml .= '<currencyId>' . $main_currency->code . '</currencyId>';
                $resp_xml .= '<categoryId>' . $p->category_id . '</categoryId>';


                // Максимальная длина — 3000 знаков.
                // Лучше уложиться в 400–800 знаков — чтобы текст не выглядел чересчур объемным и сложным для восприятия.
                if (!empty($p->annotation)) {
                    $resp_xml .= '<description>' . htmlspecialchars(strip_tags($p->annotation)) . '</description>';
                }


                // Бренд
                if (!empty($p->brand_name)) {
                    $resp_xml .= '<vendor>' . htmlspecialchars($p->brand_name) . '</vendor>';
                }


                // Price
                $price = round(FinanceCurrency::priceConvert($p->price, $main_currency->id, false), 2);
                $old_price = round(FinanceCurrency::priceConvert($p->old_price, $main_currency->id, false), 2);

                $resp_xml .= '<price>' . $price . '</price>';
                if (!empty($p->old_price) and $p->price < $p->old_price) {
                    $resp_xml .=  "<oldprice>" . $old_price . "</oldprice>";
                }


                // Options
                // Example: <param name="Размер экрана" unit="дюйм">27</param>
                $product_options = ProductOption::getProductOptions($p->product_id);
                foreach ($product_options as $option) {
                    $resp_xml .= '<param name="' . htmlspecialchars($option->name) . '">' . htmlspecialchars($option->value) . '</param>';
                }


                // Добавляйте не больше 20 фотографий для одного товара.
                // На фотографиях может использоваться водяной знак и иная информация о товаре
                $images = Image::getImages($p->product_id, 'product');
                if ($images->isNotEmpty()) {
                    foreach ($images as $img) {
                        $resp_xml .= '<picture>' . Image::getURL($img->filename, 1080, 1080, true) . '</picture>';
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
