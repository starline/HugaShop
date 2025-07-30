<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.6
 *
 */

namespace HugaShop\Extensions\BackToTop;

use HugaShop\Extensions\BaseExtension;

final class BackToTop extends BaseExtension
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
