<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.2
 *
 */

namespace HugaShop\Services;

use HugaShop\Services\Design;
use HugaShop\Models\Localization\Language;
use Symfony\Component\Translation\Translator;
use Symfony\Contracts\Translation\TranslatorInterface;

class TranslatorFactory
{

    public static $Translator;

    /**
     * Set Translator
     * @param string $locale
     * @param string $theme
     */
    public static function initTranslator(Translator|TranslatorInterface $Translator)
    {

        $theme = Design::getTheme();
        if (empty($theme)) {
            return;
        }

        $locale_code        = Language::getCurrent()->code;
        $main_locale_code   = Language::getMain()->code;

        self::$Translator = $Translator;
        self::$Translator->setLocale($locale_code);

        // Add Lovale translation file
        self::addYamlResourse(Config::get('templates_dir') . $theme . '/translations/messages.' . $locale_code . '.yaml', $locale_code);

        // If not main locale, set fallback
        if ($locale_code !== $main_locale_code) {
            self::$Translator->setFallbackLocales([$main_locale_code]);
            self::addYamlResourse(Config::get('templates_dir') . $theme . '/translations/messages.' . $main_locale_code . '.yaml', $main_locale_code);
        }

        // Сохранять список ресурсов по локали в кеш, проверять какие уже загруженны и не загружать заново.

        dump(self::$Translator->getCatalogue($locale_code));

        Design::setModifierPlugin('trans', self::class, 'translate');
    }


    /**
     * Add translation resource
     */
    public static function addYamlResourse(string $translate_file_path, ?string $locale_code = null)
    {
        if (file_exists($translate_file_path)) {
            $locale_code = $locale_code ?? Language::getCurrent()->code;
            self::$Translator->addResource('yaml', $translate_file_path, $locale_code);
        }
    }


    /**
     * Translate message
     */
    public static function translate(string $message, ?string $domain = null): string
    {
        return self::$Translator->trans($message, [], $domain);
    }
}
