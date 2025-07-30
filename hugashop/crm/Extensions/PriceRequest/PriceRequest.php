<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.1
 */

namespace HugaShop\Extensions\PriceRequest;

use HugaShop\Extensions\BaseExtension;

final class PriceRequest extends BaseExtension
{
    /**
     * Render scripts on front pages
     */
    public static function getFrontBodyTemplate()
    {
        return self::fetchTemplate('scripts.tpl');
    }
}
