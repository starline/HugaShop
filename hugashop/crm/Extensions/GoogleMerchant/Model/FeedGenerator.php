<?php

/**
 *
 * @author Andi Huga
 * @version 4.0
 *
 * Google feed generator
 * Uses Cache
 * 
 * @link https://support.google.com/merchants/answer/13580733?hl=en
 * 
 */

namespace HugaShop\Extensions\GoogleMerchant\Model;

use HugaShop\Models\Image;
use HugaShop\Models\Config;
use HugaShop\Models\Helper;
use HugaShop\Models\Settings;
use HugaShop\Models\Product\Product;
use HugaShop\Models\Product\ProductOption;
use HugaShop\Models\Finance\FinanceCurrency;
use HugaShop\Models\Product\ProductCategory;
use Symfony\Contracts\Cache\ItemInterface;
use HugaShop\Extensions\GoogleMerchant\Model\GoogleMerchantCategory;

class FeedGenerator
{
    private static $pricefeed;

    public static function getPriceFeed(object $pricefeed)
    {

        self::$pricefeed = $pricefeed; # for use in cache function

        // The callable will only be executed on a cache miss.
        $response = Helper::cache(self::class)->get('item_' . $pricefeed->id, function (ItemInterface $item): array {
            $item->expiresAfter(10); # seconds

            $feed_data = [];

            // Валюты
            $main_currency = FinanceCurrency::getMainCurrency();
            if (!empty(self::$pricefeed->currency_code)) {
                $main_currency->code = self::$pricefeed->currency_code;
            }

            $categories = GoogleMerchantCategory::getCategoriesIds(self::$pricefeed->id);

            // TODO: Select all child categories
            $filter['category_id'] = $categories;
            $filter['visible'] = 1;
            $products_raw = Product::getList($filter, 'position', ['image', 'brand']);

            // В качестве id используется артикул
            foreach ($products_raw as $product_raw) {

                $product = new \stdClass();  # clean

                // Hard Required params
                if (empty($product_raw->image) || empty($product_raw->name) || empty($product_raw->price)) {
                    continue;
                }

                // Disable
                if (!empty($product_raw->disable)) {
                    continue;
                }

                // Не показываеам "нет в наличии"
                if ($product_raw->stock == 0  and empty(self::$pricefeed->show_out_stock)) {
                    continue;
                }

                // ID
                if (empty(self::$pricefeed->sku_id) || empty($product_raw->sku)) {
                    $product->id = $product_raw->id;
                } else {
                    $product->id = $product_raw->sku; # В качестве id используется артикул
                }


                $product->link = Config::get('root_url') . '/tovar-' . $product_raw->url;
                $product->condition = 'new';

                // Форммируем название + вариант
                $product->name = $product_raw->name . ($product_raw->variant_name ? ' - ' . $product_raw->variant_name : '');

                // TIP: Если использовать основное описание товара - Слишком много нерелевантных слов.
                $product->description = strip_tags($product_raw->annotation);


                // + характеристики
                $options = ProductOption::getProductOptions($product_raw->id);
                $array_options = [];
                foreach ($options as $item) {
                    $array_options[] = $item->name . ': ' . $item->value;
                }

                if (!empty($array_options)) {
                    $product->description = $product->description ?: $product_raw->name;
                    $product->description .= ' | ' . join(', ', $array_options);
                }


                // Main image
                // Нельзя использовать изображения рекламного характера или фотографии с надписями, которые закрывают товар.
                // Водяные знаки, логотипы, названия марок и другие наложения;
                $product->image_link = Image::getURL($product_raw->image->filename, 1080, 1080);

                // Обработка дополнительных фотографий
                $images = Image::getImages($product_raw->id, 'product');
                $images = $images->slice(1)->values();
                if ($images->isNotEmpty()) {
                    $product->additional_image_link = $images->map(function ($image) {
                        return Image::getURL($image->filename, 1080, 1080);
                    });
                }


                // Цена товара со скидкой
                $price = round(FinanceCurrency::priceConvert($product_raw->price, $main_currency->id, false), 2);
                if (!is_null($product_raw->old_price) && $product_raw->old_price > $product_raw->price) {
                    $product->sale_price = $price . ' ' . $main_currency->code;
                    $price = round(FinanceCurrency::priceConvert($product_raw->old_price, $main_currency->id, false), 2);
                }
                $product->price = $price . ' ' . $main_currency->code;


                /**
                 * Категории google
                 * @link https://support.google.com/merchants/answer/6324436?sjid=3754571142713809101-NC#Format 
                 * TODO
                 */


                /**
                 * Пути к категории товара
                 * @link https://support.google.com/merchants/answer/6324406?sjid=9520931803415694483-NC
                 */
                $product_category = ProductCategory::getCategory($product_raw->category_id);
                if (!empty($product_category)) {
                    foreach ($product_category->path as $cat) {
                        $categories_array[] = $cat->name;
                    }
                    $product->product_type = join(" > ", $categories_array);
                }


                // Brand
                if (!empty($product_raw->brand->name)) {
                    $product->brand_name = $product_raw->brand->name;
                } else {
                    $product->brand_name = Settings::getParam('company_name');
                }


                // Availability
                $product->availability = 'out_of_stock';
                if (is_null($product_raw->stock) || $product_raw->stock > 0) {
                    $product->availability = 'in_stock';
                }


                /**
                 * Label
                 * @link https://support.google.com/merchants/answer/6324473?sjid=3754571142713809101-NC&visit_id=638779179732037161-207346312&rd=1
                 * Limits: 1–100 characters, up to 1,000 unique values account-wide for each custom label attribute (up to 5,000 labels total)
                 */
                if (!empty(self::$pricefeed->label)) {
                    $product->label_0 = self::$pricefeed->label;
                }

                if (!empty($product_category->url)) {
                    $product->label_1 = $product_category->url;
                }

                $feed_data[] = $product;
            }

            return $feed_data;
        });

        return $response;
    }
}
