<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.0
 */

namespace HugaShop\Extensions\TimerGetHelpOffer;

use HugaShop\Extensions\BaseExtension;

final class TimerGetHelpOffer extends BaseExtension
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
