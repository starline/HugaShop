<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.1
 *
 * Service for panguage helpers
 */

namespace App\Services;

use HugaShop\Services\Design;
use HugaShop\Services\Request;
use HugaShop\Models\Localization\Language;


class LanguageService
{

    /**
     * Init content language
     */
    public static function languageCatch()
    {
        $languages          = Language::getLanguages();
        $main_language      = Language::getMainLanguage();
        $current_language   = Language::getCurrent(Request::get('lang', 'string'));

        Design::assign('languages', $languages);
        Design::assign('main_language',  $main_language);
        Design::assign('current_language', $current_language);

        return $current_language;
    }
}
