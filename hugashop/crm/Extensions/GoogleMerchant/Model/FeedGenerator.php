<?php

/**
 *
 * @author Andi Huga
 * @version 3.8
 *
 * Google feed generator
 * Uses Cache
 * 
 * @link https://support.google.com/merchants/answer/13580733?hl=en
 * 
 */

namespace HugaShop\Extensions\GoogleMerchant\Model;

use HugaShop\Api\Image;
use HugaShop\Api\Config;
use HugaShop\Api\Helper;
use HugaShop\Api\Settings;
use HugaShop\Api\Product\ProductBrand;
use HugaShop\Api\Product\ProductOption;
use HugaShop\Api\Product\ProductVariant;
use HugaShop\Api\Finance\FinanceCurrency;
use HugaShop\Api\Product\ProductCategory;
use Symfony\Contracts\Cache\ItemInterface;
use HugaShop\Extensions\GoogleMerchant\Model\GoogleMerchantCategory;

class FeedGenerator
{
    private static $pricefeed;

    public static function getPriceFeed(object $pricefeed)
    {

        self::$pricefeed = $pricefeed; # for use in cache function

        // The callable will only be executed on a cache miss.
        $response = Helper::cache(self::class)->get($pricefeed->id, function (ItemInterface $item): array {
            $item->expiresAfter(10); # seconds


            // Валюты
            $main_currency = FinanceCurrency::getMainCurrency();
            if (!empty(self::$pricefeed->currency_code)) {
                $main_currency->code = self::$pricefeed->currency_code;
            }

            $categories = GoogleMerchantCategory::getCategoriesIds(self::$pricefeed->id);

            // TODO: Select all child categories
            $filter['category_id'] = $categories;
            $filter['visible'] = 1;
            $product_variants = ProductVariant::getVariants($filter, ['product_id' => 'ASC', 'position' => 'ASC'], ['Product', 'Image', 'ProductBrand', 'ProductCategory']);

            // В качестве id используется артикул
            $products = [];
            $prev_product_id = null;
            foreach ($product_variants as $pv) {

                $product = new \stdClass();  # clean

                // Hard Required params
                if (empty($pv->image) || empty($pv->product_name) || empty($pv->price)) {
                    continue;
                }

                // Disable
                if (!empty($pv->disable)) {
                    continue;
                }

                // Не показываеам "нет в наличии"
                if ($pv->stock == 0  and empty(self::$pricefeed->show_out_stock)) {
                    continue;
                }

                $variant_url = '';
                if ($prev_product_id === $pv->product_id) {
                    $variant_url = '?variant=' . $pv->variant_id;
                }
                $prev_product_id = $pv->product_id;


                // ID
                if (empty(self::$pricefeed->sku_id) || empty($pv->sku)) {
                    $product->id = $pv->variant_id;
                } else {
                    $product->id = $pv->sku; # В качестве id используется артикул
                }


                $product->link = Config::get('root_url') . '/tovar-' . $pv->url . $variant_url;
                $product->condition = 'new';


                // Форммируем название + вариант
                $product->name = $pv->product_name . ($pv->variant_name ? ' - ' . $pv->variant_name : '');


                // TIP: Если использовать основное описание товара - Слишком много нерелевантных слов.
                $product->description = strip_tags($pv->annotation);


                // + характеристики
                $options = ProductOption::getProductOptions($pv->product_id);
                $array_options = [];
                foreach ($options as $item) {
                    $array_options[] = $item->name . ': ' . $item->value;
                }

                if (!empty($array_options)) {
                    $product->description = $product->description ?: $pv->product_name;
                    $product->description .= ' | ' . join(', ', $array_options);
                }


                // Main image
                // Нельзя использовать изображения рекламного характера или фотографии с надписями, которые закрывают товар.
                // Водяные знаки, логотипы, названия марок и другие наложения;
                $product->image_link = Image::getURL($pv->image, 1080, 1080);

                // Обработка дополнительных фотографий
                $images = Image::getImages($pv->product_id, 'product');
                $images = $images->slice(1)->values();
                if (!$images->isNotEmpty()) {
                    $product->additional_image_link = $images->map(function ($image) {
                        return Image::getURL($image->filename, 1080, 1080);
                    });
                }


                // Цена товара со скидкой
                $price = round(FinanceCurrency::priceConvert($pv->price, $main_currency->id, false), 2);
                if (!is_null($pv->old_price) && $pv->old_price > $pv->price) {
                    $product->sale_price = $price . ' ' . $main_currency->code;
                    $price = round(FinanceCurrency::priceConvert($pv->old_price, $main_currency->id, false), 2);
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
                $categories = ProductCategory::getCategories(['product_id' => $pv->product_id]);
                if (!empty($categories)) {
                    $categories_array = [];
                    $categories = reset($categories);
                    foreach ($categories->path as $category) {
                        $categories_array[] = $category->name;
                    }

                    $product->product_type = join(" > ", $categories_array);
                }


                // Brand
                if (!empty($pv->brand_id)) {
                    $brandItem = ProductBrand::getBrand((int)$pv->brand_id);
                    if (!is_null($brandItem)) {
                        $product->brand_name = $brandItem->name;
                    }
                }

                if (empty($product->brand_name)) {
                    $product->brand_name = Settings::getParam('company_name');
                }


                // Availability
                $product->availability = 'out_of_stock';
                if (is_null($pv->stock) || $pv->stock > 0) {
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

                if (!empty($pv->category_url)) {
                    $product->label_1 = $pv->category_url;
                }

                $products[] = $product;
            }

            return $products;
        });

        return $response;
    }
}
