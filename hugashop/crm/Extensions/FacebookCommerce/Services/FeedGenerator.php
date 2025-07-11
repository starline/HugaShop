<?php

/**
 *
 * @author Andri Huga
 * @version 1.8
 *
 * Facebook feed generator
 * Uses Cache
 * 
 * Data feed fields and specifications for catalogs in Commerce Manager
 * @link https://www.facebook.com/business/help/120325381656392?id=725943027795860
 * 
 * id                           - # Required | A unique content ID for the item. Use the item's SKU if you can. Each content ID must appear only once in your catalog. To run dynamic ads this ID must exactly match the content ID for the same item in your Meta Pixel code. Character limit: 100
 * title                        - # Required | A specific and relevant title for the item. See title specifications: https://www.facebook.com/business/help/2104231189874655 Character limit: 200
 * description                  - # Required | A short and relevant description of the item. Include specific or unique product features like material or color. Use plain text and don't enter text in all capital letters. See description specifications: https://www.facebook.com/business/help/2302017289821154 Character limit: 9999
 * availability                 - # Required | The current availability of the item. | Supported values: in stock; out of stock
 * condition                    - # Required | The current condition of the item. | Supported values: new; used
 * price                        - # Required | The price of the item. Format the price as a number followed by the 3-letter currency code (ISO 4217 standards). Use a period (.) as the decimal point; don't use a comma.
 * link                         - # Required | The URL of the specific product page where people can buy the item.
 * image_link                   - # Required | The URL for the main image of your item. Images must be in a supported format (JPG/GIF/PNG) and at least 500 x 500 pixels.
 * brand                        - # Required | The brand name of the item. Character limit: 100.
 * 
 * google_product_category      - # Optional | The Google product category for the item. Learn more about product categories: https://www.facebook.com/business/help/526764014610932.
 * fb_product_category          - # Optional | The Facebook product category for the item. Learn more about product categories: https://www.facebook.com/business/help/526764014610932.
 * quantity_to_sell_on_facebook - # Optional | The quantity of this item you have to sell on Facebook and Instagram with checkout. Must be 1 or higher or the item won't be buyable
 * sale_price                   - # Optional | The discounted price of the item if it's on sale. Format the price as a number followed by the 3-letter currency code (ISO 4217 standards). Use a period (.) as the decimal point; don't use a comma. A sale price is required if you want to use an overlay for discounted prices.
 * sale_price_effective_date    - # Optional | The time range for your sale period. Includes the date and time/time zone when your sale starts and ends. If this field is blank any items with a sale_price remain on sale until you remove the sale price. Use this format: YYYY-MM-DDT23:59+00:00/YYYY-MM-DDT23:59+00:00. Enter the start date as YYYY-MM-DD. Enter a 'T'. Enter the start time in 24-hour format (00:00 to 23:59) followed by the UTC time zone (-12:00 to +14:00). Enter '/' and then repeat the same format for your end date and time. The example row below uses PST time zone (-08:00).
 * item_group_id                - # Optional | Use this field to create variants of the same item. Enter the same group ID for all variants within a group. Learn more about variants: https://www.facebook.com/business/help/2256580051262113 Character limit: 100.
 * gender                       - # Optional | The gender of a person that the item is targeted towards. | Supported values: female; male; unisex
 * color                        - # Optional | The color of the item. Use one or more words to describe the color. Don't use a hex code. Character limit: 200.
 * size                         - # Optional | The size of the item written as a word or abbreviation or number. For example: small; XL; 12. Character limit: 200.
 * age_group                    - # Optional | The age group that the item is targeted towards. | Supported values: adult; all ages; infant; kids; newborn; teen; toddler
 * material                     - # Optional | The material the item is made from; such as cotton; denim or leather. Character limit: 200.
 * pattern                      - # Optional | The pattern or graphic print on the item. Character limit: 100.
 * shipping                     - # Optional | Shipping details for the item. Format as Country:Region:Service:Price. Include the 3-letter ISO 4217 currency code in the price. Enter the price as 0.0 to use the free shipping overlay in your ads. Use a semi-colon ';' or a comma ";" to separate multiple shipping details for different regions or countries. Only people in the specified region or country will see shipping details for that region or country. You can leave out the region (keep the double '::') if your shipping details are the same for an entire country.
 * shipping_weight              - # Optional | The shipping weight of the item. Include the unit of measurement (lb/oz/g/kg).
 * gtin                         - # Optional | The item’s Global Trade Item Number (GTIN). Recommended to help classify the item. May appear on the barcode; packaging or book cover. Only provide GTIN if you’re sure it’s correct. GTIN types include UPC (12 digits); EAN (13 digits); JAN (8 or 13 digits); ISBN (13 digits) or ITF-14 (14 digits)
 * video[0].url                 - # Optional | URLs and tags for videos to be used in your ads or in shops. Supports up to 20 different videos. Must be a direct link to download the video file; not a link to a video player such as YouTube. Tags are optional and; if used; should describe what is in the video. Learn more about video field specifications at: https://www.facebook.com/business/help/120325381656392
 * video[0].tag[0]              - # Optional | URLs and tags for videos to be used in your ads or in shops. Supports up to 20 different videos. Must be a direct link to download the video file; not a link to a video player such as YouTube. Tags are optional and; if used; should describe what is in the video. Learn more about video field specifications at: https://www.facebook.com/business/help/120325381656392
 * product_tags[0]              - # Optional | Add labels to products to help filter them into product sets. Max characters: 110 per label; 5000 labels per product
 * product_tags[1]              - # Optional | Add labels to products to help filter them into product sets. Max characters: 110 per label; 5000 labels per product
 * style[0]                     - # Optional | Describe the fashion style of this item.
 * 
 */

namespace HugaShop\Extensions\FacebookCommerce\Services;

use HugaShop\Models\Image;
use HugaShop\Services\Cache;
use HugaShop\Models\Settings;
use HugaShop\Services\Config;
use HugaShop\Models\Product\Product;
use HugaShop\Models\Product\ProductOption;
use Symfony\Contracts\Cache\ItemInterface;
use HugaShop\Models\Finance\FinanceCurrency;
use HugaShop\Models\Product\ProductCategory;
use HugaShop\Extensions\FacebookCommerce\Models\FacebookCommerceCategory;

class FeedGenerator
{
    private static $pricefeed;

    public static function getPriceFeed(object $pricefeed)
    {

        self::$pricefeed = $pricefeed; # for use in cache function

        // The callable will only be executed on a cache miss.
        $response = Cache::cache(self::class)->get('item_' . $pricefeed->id, function (ItemInterface $item): array {
            $item->expiresAfter(10); # seconds

            // Валюты
            $main_currency = FinanceCurrency::getMainCurrency();
            if (!empty(self::$pricefeed->currency_code)) {
                $main_currency->code = self::$pricefeed->currency_code;
            }

            $pricefeed_categories = FacebookCommerceCategory::getCategoriesIds(self::$pricefeed->id);

            // Include children categories
            $categories = [];
            foreach ($pricefeed_categories as $category_id) {
                $category = ProductCategory::getCategory($category_id);
                if (!empty($category->children)) {
                    $categories = array_merge($categories, $category->children);
                }
            }
            $filter['category_id'] = array_values(array_unique($categories ?: $pricefeed_categories));
            $filter['visible'] = 1;
            $products = Product::getList($filter, order: 'position', join: ['image', 'brand']);

            $feed_data = [];
            foreach ($products as $product_raw) {

                // Hard Required params
                if (empty($product_raw->image) || empty($product_raw->name) || empty($product_raw->price)) {
                    continue;
                }

                // Disable
                if (!empty($product_raw->disable)) {
                    continue;
                }

                // Не показываеам "нет в наличии"
                if ($product_raw->stock === 0  and empty(self::$pricefeed->show_out_stock)) {
                    continue;
                }

                // ID
                if (empty(self::$pricefeed->sku_id) || empty($product_raw->sku)) {
                    $product['id'] = $product_raw->id;
                } else {
                    $product['id'] = $product_raw->sku; # В качестве id используется артикул
                }


                $product['link'] = Config::get('root_url') . '/tovar-' . $product_raw->url;
                $product['condition'] =  'new';


                // Формируем название + вариант
                // Information in these fields should be 65 characters or less
                $product['title'] = mb_substr($product_raw->name . ($product_raw->variant_name ? ' - ' . $product_raw->variant_name : ''), 0, 65);


                // TIP: Если использовать основное описание товара - Слишком много нерелевантных слов.
                // Items need to have descriptions to show in your shop and ads.
                $product['description'] = $product_raw->annotation ? strip_tags($product_raw->annotation) : $product['title'];


                // + характеристики
                $options = ProductOption::getProductOptions($product_raw->id);
                $array_options = [];
                foreach ($options as $item) {
                    $array_options[] = $item->name . ': ' . $item->value;
                }

                if (!empty($array_options)) {
                    $product['description'] = $product['description'] ?: $product['title'];
                    $product['description'] .= ' | ' . join(', ', $array_options);
                }


                // Main image
                // Don't include text in your images that overlays the product, calls to action, promo codes, watermarks or time-sensitive information like temporary price drops.
                // Images must be in JPEG or PNG format, at least 500 x 500 pixels and up to 8 MB
                $product['image_link'] = Image::getImageURL($product_raw->image->filename, 1080, 1080);


                // Additional images
                // Links to up to 20 additional images of your item, separated by a comma (,), semicolon (;), space ( ) or vertical bar (|).
                // Follow the same image specifications as image_link.
                $images = Image::getImages($product_raw->id, 'product', true);
                $images = $images->slice(1)->values();
                if ($images->isNotEmpty()) {
                    $product['additional_image_link'] = $images->map(function ($image) {
                        return Image::getImageURL($image->filename, 1080, 1080);
                    })->implode('|');
                }


                // Цена товара со скидкой
                $price = round(FinanceCurrency::priceConvert($product_raw->price, $main_currency->id, false), 2);
                if (!is_null($product_raw->old_price) && $product_raw->old_price > $product_raw->price) {
                    $product['sale_price'] = $price . ' ' . $main_currency->code;
                    $price = round(FinanceCurrency::priceConvert($product_raw->old_price, $main_currency->id, false), 2);
                }
                $product['price'] =  $price . ' ' . $main_currency->code;


                /**
                 * Пути к категории товара
                 * @link https://support.google.com/merchants/answer/6324436?sjid=3754571142713809101-NC#Format
                 */
                $product_category = ProductCategory::getCategory($product_raw->category_id);
                if (!empty($product_category)) {
                    $categories_array = [];
                    foreach ($product_category->path as $category) {
                        $categories_array[] = $category->name;
                    }
                    $product['google_product_category'] =  join(" > ", $categories_array);
                }


                // Brand
                if (!empty($product_raw->brand->name)) {
                    $product['brand'] = $product_raw->brand->name;
                } else {
                    $product['brand'] = Settings::getParam('company_name');
                }


                // Availability
                if (!is_null($product_raw->stock)) {
                    if ($product_raw->stock > 0) {
                        $product['availability'] = 'in stock';
                    } else {
                        $product['availability'] = 'out of stock';
                    }
                }


                // Label
                // Enclose each label in single quotes (') and separate multiple labels with commas (,).
                // Don’t include white space at the beginning or end of a label.
                // Character limit: 5,000 labels per product and 110 characters per label.
                // Example: ['summer','trending']
                if (!empty(self::$pricefeed->label)) {
                    $product['internal_label'] = "['" . self::$pricefeed->label . "']";
                }

                $feed_data[] = $product;
            }

            return $feed_data;
        });

        return $response;
    }
}
