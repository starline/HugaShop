<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.2
 * 
 */

namespace HugaShop\Extensions\ProductFilling\Models;

use HugaShop\Models\Product\Product as ProductBase;

final class Product extends ProductBase
{


    /**
     * Get Products 
     */
    public static function getProducts(array $filter = [], array $join = [], bool $count = false)
    {
        return parent::getProducts($filter, $join, $count);
    }
}
