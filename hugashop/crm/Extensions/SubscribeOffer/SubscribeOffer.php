<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.0
 */

namespace HugaShop\Extensions\SubscribeOffer;

use HugaShop\Models\User\User;
use HugaShop\Extensions\BaseExtension;

final class SubscribeOffer extends BaseExtension
{
    /**
     * Render scripts on front pages
     */
    public static function getFrontBodyTemplate()
    {
        if (self::isEnabled() && !User::isLoggedIn()) {
            return self::fetchTemplate('scripts.tpl');
        }
        return;
    }
}
