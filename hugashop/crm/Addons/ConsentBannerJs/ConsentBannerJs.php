<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.6
 * 
 * @link https://github.com/tagconcierge/consent-banner-js
 *
 */

namespace HugaShop\Addons\ConsentBannerJs;

use HugaShop\Services\Design;
use HugaShop\Addons\BaseAddon;
use HugaShop\Models\Localization\Language;

final class ConsentBannerJs extends BaseAddon
{

    /**
     * Get block template
     */
    public static function getFrontBodyTemplate()
    {
        if (self::isEnabled()) {

            // If Trnaslation file exists
            $translate_file_path = self::getAddonDir() . 'translations/messages.' . Language::getCurrent()->code . '.yaml';
            if (file_exists($translate_file_path)) {
                Design::$Translator->addResource('yaml', $translate_file_path, Language::getCurrent()->code);
            }

            return self::fetchTemplate('banner.tpl');
        }
    }
}
