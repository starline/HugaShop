<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.2
 */

namespace HugaShop\Addons\ProductPriceRequest;

use HugaShop\Addons\BaseAddon;

final class ProductPriceRequest extends BaseAddon
{

    /**
     * Render scripts on front pages
     */
    public static function getFrontBodyTemplate()
    {
        if (self::isEnabled()) {
            return self::fetchTemplate('scripts.tpl');
        }
        return;
    }
}
