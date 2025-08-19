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


    /**
     * Get block template
     */
    public static function getFrontBodyTemplate()
    {
        if (self::isEnabled()) {
            Design::assign('storage_key', 'language_auto_detected');
            return self::fetchTemplate('switcher.tpl');
        }
    }
}
