<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.2
 *
 */

namespace HugaShop\Extensions\ProductBrowsed;

use HugaShop\Extensions\BaseExtension;

final class ProductBrowsed extends BaseExtension
{


    /**
     * Get Head template
     */
    public static function getTemplate()
    {
        if (!empty(self::getSettings()->enabled)) {
            return self::fetchTemplate('templates/product_browsed.tpl');
        }
        return;
    }
}
