<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.1
 */

namespace HugaShop\Extensions\ProductPriceRequest;

use HugaShop\Extensions\BaseExtension;

final class ProductPriceRequest extends BaseExtension
{
    /**
     * Render scripts on front pages
     */
    public static function getFrontBodyTemplate()
    {
        return self::fetchTemplate('scripts.tpl');
    }
}
