<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.2
 *
 * Detect browser language and suggest switching.
 * 
 */

namespace HugaShop\Extensions\LanguageAutoDetect;

use HugaShop\Extensions\BaseExtension;
use HugaShop\Services\Design;

final class LanguageAutoDetect extends BaseExtension
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
