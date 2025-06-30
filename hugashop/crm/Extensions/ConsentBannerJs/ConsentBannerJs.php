<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.2
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
    public function getFrontBodyTemplate()
    {
        if (!empty($this->settings->enabled)) {

            // If Trnaslation file exists
            $translate_file_path = $this->getExtensionDir() . 'translations/messages.' . Design::$locale . '.yaml';
            if (file_exists($translate_file_path)) {
                Design::$Translator->addResource('yaml', $translate_file_path, Design::$locale);
            }

            return $this->fetchTemplate('banner.tpl');
        }
    }
}
