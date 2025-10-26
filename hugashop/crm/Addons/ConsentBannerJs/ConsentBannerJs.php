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

use HugaShop\Addons\BaseAddon;
use HugaShop\Models\Localization\Language;
use HugaShop\Services\TranslatorFactory;

final class ConsentBannerJs extends BaseAddon
{

    /**
     * Get block template
     */
    public static function getFrontBodyTemplate()
    {
        if (self::isEnabled()) {
            TranslatorFactory::addYamlResourse(self::getAddonDir() . 'translations/messages.' . Language::getCurrent()->code . '.yaml');
            return self::fetchTemplate('banner.tpl');
        }
    }
}
