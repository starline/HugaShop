<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.4
 * 
 */

namespace HugaShop\Addons\ProductsImport\Services;

use HugaShop\Models\Product\Product;
use HugaShop\Models\Warehouse\WarehousePlaceProduct;

final class ProductImport
{

    public static function importItem(array $item, int $place_id): array
    {
        if (empty($item['sku']) || empty($item['amount'])) {
            return [];
        }

        foreach (['price', 'cost_price', 'amount'] as $k) {
            if (isset($item[$k])) {
                $item[$k] = str_replace(['\xc2\xa0', ','], ['', '.'], strval($item[$k]));
            }
        }

        
        $products = Product::getList(['sku' => $item['sku']]);
        
        $result = [];
        foreach ($products as $product) {
            $amount = (int) $item['amount'];

            Product::changeAmount($product->id, $amount);
            WarehousePlaceProduct::changeAmount($product->id, $place_id, $amount);

            $it = new \stdClass();
            $it->product = Product::getOne($product->id);
            $it->amount = $amount;
            $it->status = 'added';
            $result[] = $it;
        }

        if ($products->isEmpty()) {
            $err = new \stdClass();
            $err->error = 'Product not found: ' . $item['sku'];
            $result[] = $err;
        }

        return $result;
    }


    /**
     * Get column name
     */
    public static function internalColumnName(string $name, array $columns_names)
    {
        $name = trim(str_replace(['/', '\\'], '', $name));
        foreach ($columns_names as $i => $names) {
            foreach ($names as $n) {
                if ($name !== '' && preg_match("/^" . preg_quote($name) . "$/ui", $n)) {
                    return $i;
                }
            }
        }
        return false;
    }


    /**
     * Convert file
     */
    public static function convertFile(string $source, string $dest): bool
    {
        $teststring = file_get_contents($source, false, null, 0, 1000000);
        if (preg_match('//u', $teststring)) {
            return copy($source, $dest);
        }

        $src = fopen($source, 'r');
        $dst = fopen($dest, 'w');
        if (!$src || !$dst) {
            return false;
        }

        while (($line = fgets($src, 4096)) !== false) {
            $line = self::winToUTF($line);
            fwrite($dst, $line);
        }

        fclose($src);
        fclose($dst);
        return true;
    }


    /**
     * Win to UTF
     */
    public static function winToUTF(string $text): string
    {
        if (function_exists('iconv')) {
            return @iconv('windows-1251', 'UTF-8', $text);
        }

        $t = '';
        for ($i = 0, $m = strlen($text); $i < $m; $i++) {
            $c = ord($text[$i]);
            if ($c <= 127) {
                $t .= chr($c);
                continue;
            }
            if ($c >= 192 && $c <= 207) {
                $t .= chr(208) . chr($c - 48);
                continue;
            }
            if ($c >= 208 && $c <= 239) {
                $t .= chr(208) . chr($c - 48);
                continue;
            }
            if ($c >= 240 && $c <= 255) {
                $t .= chr(209) . chr($c - 112);
                continue;
            }
            if ($c == 184) {
                $t .= chr(209) . chr(145);
                continue;
            }
            if ($c == 168) {
                $t .= chr(208) . chr(129);
                continue;
            }
        }
        return $t;
    }
}
