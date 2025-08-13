<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.0
 */

namespace HugaShop\Extensions\GoogleAuth;

use HugaShop\Extensions\BaseExtension;

final class GoogleAuth extends BaseExtension
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
