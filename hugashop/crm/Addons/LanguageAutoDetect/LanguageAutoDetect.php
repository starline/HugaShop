<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.3
 *
 * Detect browser language and suggest switching.
 * 
 */

namespace HugaShop\Addons\LanguageAutoDetect;

use HugaShop\Addons\BaseAddon;
use HugaShop\Services\Design;

final class LanguageAutoDetect extends BaseAddon
{

    public static $storage_key = 'language_auto_detected';

    /**
     * Get block template
     */
    public static function getFrontBodyTemplate()
    {
        if (self::isEnabled()) {
            Design::assign('storage_key', self::$storage_key);
            return self::fetchTemplate('switcher.tpl');
        }
    }
}
