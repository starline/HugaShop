<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.5
 * 
 * @link https://github.com/tagconcierge/consent-banner-js
 *
 */

namespace HugaShop\Addons\ConsentBannerJs;

use HugaShop\Services\Design;
use HugaShop\Addons\BaseAddon;

final class ConsentBannerJs extends BaseAddon
{

    /**
     * Get block template
     */
    public static function getFrontBodyTemplate()
    {
        if (self::isEnabled()) {

            // If Trnaslation file exists
            $translate_file_path = self::getAddonDir() . 'translations/messages.' . Design::$locale . '.yaml';
            if (file_exists($translate_file_path)) {
                Design::$Translator->addResource('yaml', $translate_file_path, Design::$locale);
            }

            return self::fetchTemplate('banner.tpl');
        }
    }
}
