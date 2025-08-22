<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.1
 */

namespace HugaShop\Addons\HelpOffer;

use HugaShop\Addons\BaseAddon;

final class HelpOffer extends BaseAddon
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
