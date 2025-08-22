<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.1
 */

namespace HugaShop\Addons\GoogleAuth;

use HugaShop\Addons\BaseAddon;

final class GoogleAuth extends BaseAddon
{
    /**
     * Render Google login button
     */
    public static function getTemplate(array $params = [])
    {
        if (!self::isEnabled()) {
            return;
        }
        return self::fetchTemplate('button.tpl');
    }
}
