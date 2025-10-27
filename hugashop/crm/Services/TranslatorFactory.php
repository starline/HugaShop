<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.4
 *
 */

namespace HugaShop\Services;

use HugaShop\Services\Config;
use HugaShop\Services\Design;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Config\ConfigCache;
use HugaShop\Models\Localization\Language;
use Symfony\Component\Translation\Translator;
use Symfony\Contracts\Translation\TranslatorInterface;

class TranslatorFactory
{

    public static $Translator;

    // List of resources cached by locale
    private static array $locale_resources = [];


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

        self::$locale_resources = self::loadLocaleResources();
        dump(self::$locale_resources);

        // Add Locale translation file
        self::addYamlResource(Config::get('templates_dir') . $theme . '/translations/messages.' . $locale_code . '.yaml', $locale_code);

        // If not main locale, set fallback
        if ($locale_code !== $main_locale_code) {
            self::$Translator->setFallbackLocales([$main_locale_code]);
            self::addYamlResource(Config::get('templates_dir') . $theme . '/translations/messages.' . $main_locale_code . '.yaml', $main_locale_code);
        }

        // Пишет в кэш. если нет каталога, создает пустой кеш
        // Удаляет кеш если есть изменненния в ресурсах
        $catalogue = self::$Translator->getCatalogue($locale_code);

        if ($catalogue->all() === [] and !empty(self::$locale_resources[$locale_code])) {
            self::resetTranslatorCatalogues(self::$Translator);
            self::rebuildLocaleCatalogueCache($locale_code);
            self::$Translator->getCatalogue($locale_code);
        }

        Design::setModifierPlugin('trans', self::class, 'translate');
    }


    /**
     * Translate message
     * @param string $message
     * @param string|null $domain
     */
    public static function translate(string $message, ?string $domain = null,  ?string $locale = null): string
    {
        return self::$Translator->trans($message, [], $domain, $locale);
    }


    /**
     * Add translation resource
     */
    public static function addYamlResource(string $translate_file_path, ?string $locale_code = null)
    {
        $locale_code        = $locale_code ?? Language::getCurrent()->code;
        $resources          = self::$locale_resources[$locale_code] ?? [];
        $resource_exists    = in_array($translate_file_path, $resources, true);

        // Store loaded resources
        if (!$resource_exists and file_exists($translate_file_path)) {
            self::$locale_resources[$locale_code][] = $translate_file_path;
            self::saveLocaleResources();
            self::rebuildLocaleCatalogueCache($locale_code);
        }
    }


    /**
     * Store locale resources list in cache file
     */
    private static function saveLocaleResources(): void
    {
        $php_cache_file = self::getTranslationsCacheDir() . '/resources.php';
        $cache = new ConfigCache($php_cache_file, false); # false = production mode

        // Код, который будет записан в PHP-файл
        $content = '<?php return ' . var_export(self::$locale_resources, true) . ';';
        $cache->write($content);
    }


    /**
     * Загрузить список ресурсов локали из кеша
     */
    private static function loadLocaleResources(): array
    {
        $php_cache_file = self::getTranslationsCacheDir() . '/resources.php';
        return file_exists($php_cache_file) ? include $php_cache_file : [];
    }


    /**
     * Rebuild catalogue cache for provided locale
     */
    private static function rebuildLocaleCatalogueCache(string $locale): void
    {
        self::cleanCache($locale);

        // Добавить все ресурсы заново
        foreach (self::$locale_resources[$locale] as $resource) {
            self::$Translator->addResource('yaml', $resource, $locale);
        }
    }


    /**
     * Clean translations cache for provided locale
     */
    private static function cleanCache(string $locale): void
    {
        $cache_dir = self::getTranslationsCacheDir();

        $finder = new Finder();
        $finder->files()->in($cache_dir)->name('catalogue.' . $locale . '.*');

        foreach ($finder as $file) {
            $path = $file->getRealPath();

            // Сначала инвалидируем OPcache
            if (function_exists('opcache_invalidate')) {
                @opcache_invalidate($path, true);
            }

            // Затем удаляем файл
            @unlink($path);
        }
    }


    /**
     * Get translations cache dir path
     */
    private static function getTranslationsCacheDir(): string
    {
        $app_env = getenv('APP_ENV');
        if ($app_env === false || $app_env === '') {
            $app_env = $_SERVER['APP_ENV'] ?? $_ENV['APP_ENV'] ?? 'prod';
        }

        return rtrim(Config::get('cache_dir'), '/') . '/' . $app_env . '/translations/';
    }

    /**
     * Reset translator catalogues property
     */
    private static function resetTranslatorCatalogues(TranslatorInterface $translator): void
    {
        $reflection = new \ReflectionClass($translator);

        // Если это LoggingTranslator (dev)
        if ($reflection->hasProperty('translator')) {
            $prop = $reflection->getProperty('translator');
            $prop->setAccessible(true);
            $inner_translator = $prop->getValue($translator);

            if ($inner_translator instanceof TranslatorInterface) {
                self::resetTranslatorCatalogues($inner_translator);
                return;
            }
        }

        // Если это реальный Translator
        if ($reflection->hasProperty('catalogues')) {
            $prop = $reflection->getProperty('catalogues');
            $prop->setAccessible(true);
            $prop->setValue($translator, []);
        }
    }
}
