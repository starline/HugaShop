<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.3
 *
 */

namespace HugaShop\Addons\ProductBrowsed;

use HugaShop\Addons\BaseAddon;

final class ProductBrowsed extends BaseAddon
{


    /**
     * Get Head template
     */
    public static function getTemplate()
    {
        if (self::isEnabled()) {
            return self::fetchTemplate('product_browsed.tpl');
        }
        return;
    }
}
