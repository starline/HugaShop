<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.1
 *
 * Detect browser language and suggest switching.
 */

namespace HugaShop\Extensions\LanguageAutoDetect;

use HugaShop\Extensions\BaseExtension;
use HugaShop\Models\Localization\Language;
use HugaShop\Services\Design;

final class LanguageAutoDetect extends BaseExtension
{
    /**
     * Get block template
     */
    public static function getFrontBodyTemplate()
    {
        if (self::isEnabled()) {
            // If translation file exists
            $translate_file_path = self::getExtensionDir() . 'translations/messages.' . Design::$locale . '.yaml';
            if (file_exists($translate_file_path)) {
                Design::$Translator->addResource('yaml', $translate_file_path, Design::$locale);
            }

            Design::assign('LanguageAutoDetect_languages', Language::getLanguages());
            return self::fetchTemplate('switcher.tpl');
        }
    }
}
