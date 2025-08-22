<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.1
 */

namespace HugaShop\Addons\SubscribeOffer;

use HugaShop\Models\User\User;
use HugaShop\Addons\BaseAddon;

final class SubscribeOffer extends BaseAddon
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
