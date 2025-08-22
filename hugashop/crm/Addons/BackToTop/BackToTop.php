<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.7
 *
 */

namespace HugaShop\Addons\BackToTop;

use HugaShop\Addons\BaseAddon;

final class BackToTop extends BaseAddon
{

    /**
     * Get Head template
     */
    public static function getFrontBodyTemplate()
    {
        if (self::isEnabled()) {
            return self::fetchTemplate('button.tpl');
        }
        return;
    }
}
