<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.4
 * 
 * @link https://github.com/tagconcierge/consent-banner-js
 *
 */

namespace HugaShop\Extensions\ConsentBannerJs;

use HugaShop\Services\Design;
use HugaShop\Extensions\BaseExtension;

final class ConsentBannerJs extends BaseExtension
{

    /**
     * Get block template
     */
    public static function getFrontBodyTemplate()
    {
        if (self::isEnabled()) {

            // If Trnaslation file exists
            $translate_file_path = self::getExtensionDir() . 'translations/messages.' . Design::$locale . '.yaml';
            if (file_exists($translate_file_path)) {
                Design::$Translator->addResource('yaml', $translate_file_path, Design::$locale);
            }

            return self::fetchTemplate('banner.tpl');
        }
    }
}
