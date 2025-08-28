<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.4
 * 
 */

namespace HugaShop\Addons\ProductFilling\Services;

use HugaShop\Models\Product\Product;
use HugaShop\Models\Localization\Language;
use HugaShop\Addons\ProductFilling\Models\ProductFilling;

final class Calculate
{


    /**
     * Calculate filling for one product
     */
    public static function calculateProduct(int $product_id)
    {
        $product = Product::getProduct($product_id);
        if (!$product) {
            return;
        }

        $fields = ['name', 'meta_title', 'meta_description', 'annotation', 'body'];
        $langs = Language::getLanguages();
        $language_codes = $langs->pluck('code')->toArray();

        // Remove filling data for languages absent in the system
        ProductFilling::query()
            ->where('product_id', $product_id)
            ->whereNotIn('language_code', $language_codes)
            ->delete();

        foreach ($langs as $lang) {
            $filled = 0;
            if ($lang->main) {
                foreach ($fields as $field) {
                    if (!empty(trim($product->$field))) {
                        $filled++;
                    }
                }
            } else {
                $translation = Product::getTranslation($product_id, $lang->code);
                foreach ($fields as $field) {
                    $val = $translation->$field ?? null;
                    if (!empty(trim($val))) {
                        $filled++;
                    }
                }
            }

            $percent = intval($filled / count($fields) * 100);
            ProductFilling::updateOrCreate([
                'product_id' => $product_id,
                'language_code' => $lang->code
            ], [
                'percent' => $percent
            ]);
        }
    }


    /**
     * Recalculate filling for all products
     */
    public static function calculateAllProducts()
    {
        $ids = Product::getList(select: 'id');
        foreach ($ids as $id) {
            self::calculateProduct($id);
        }
    }
}
