<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.2
 *
 */

namespace HugaShop\Addons\SeoLinker\Services\DomParser;

use Throwable;
use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\Psr7\UriResolver;
use Symfony\Component\DomCrawler\Crawler;

final class LinksParser
{
    private static $base_host;
    private static $base_scheme;
    private static $links = [];

    public static function get(Crawler $dom, string $current_url, string $base_host, string $base_scheme)
    {

        self::$base_host   = strtolower($base_host);
        self::$base_scheme = strtolower($base_scheme);

        $out_internal = 0;
        $out_external = 0;

        $dom->filter('a[href]')->each(function (Crawler $node) use (&$out_internal, &$out_external,  $current_url) {

            $href = trim((string) ($node->attr('href') ?? ''));
            if ($href === '') {
                return;
            }

            // rel может быть пустым; нормализуем к нижнему регистру
            $rel_attr = (string) $node->attr('rel') ?? '';
            $rel      = strtolower(trim($rel_attr));
            $nofollow = ($rel !== '' && str_contains($rel, 'nofollow'));

            // Пропуски явно не-кликабельных кейсов
            $href_lc = strtolower($href);
            if (
                str_starts_with($href, '#') ||
                str_starts_with($href_lc, 'javascript:') ||
                str_starts_with($href_lc, 'mailto:') ||
                str_starts_with($href_lc, 'tel:') ||
                str_starts_with($href_lc, 'tg:')
            ) {
                return;
            }

            // Абсолютный URL
            $abs = self::absoluteUrl($href, $current_url);
            if (!$abs || !is_string($abs)) {
                return;
            }

            // Картинки по расширению
            $path       = (string) (parse_url($abs, PHP_URL_PATH) ?? '');
            $ext        = strtolower((string) pathinfo($path, PATHINFO_EXTENSION));
            $is_image   = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp', 'tif', 'tiff'], true);

            if ($is_image) {
                self::$links[] = [
                    'from_url' => $current_url,
                    'to_url'   => $abs,
                    'type'     => 'image',
                    'nofollow' => (int) $nofollow,
                ];
            }

            // Хост/схема
            $p       = parse_url($abs) ?: [];
            $host    = strtolower((string) ($p['host'] ?? ''));
            $scheme  = strtolower((string) ($p['scheme'] ?? '')); # может быть пустым после absoluteUrl, но обычно уже нормализован

            // Внутренняя ссылка
            if ($host === self::$base_host && ($scheme === '' || $scheme === self::$base_scheme)) {
                $out_internal++;
                self::$links[] = [
                    'from_url' => $current_url,
                    'to_url'   => $abs,
                    'type'     => 'internal',
                    'nofollow' => (int) $nofollow,
                ];
            }

            // Векшеяя ссылка
            else {
                $out_external++;
                self::$links[] = [
                    'from_url' => $current_url,
                    'to_url'   => $abs,
                    'type'     => 'external',
                    'nofollow' => (int) $nofollow,
                ];
            }
        });

        return [$out_internal, $out_external, self::$links];
    }


    /**
     * Make absolute url
     */
    private static function absoluteUrl(string $href, string $base): ?string
    {
        if (preg_match('/^https?:\/\//i', $href)) {
            return $href;
        }

        if (str_starts_with($href, '//')) {
            return self::$base_scheme . ':' . $href;
        }

        if ($href === '') {
            return null;
        }

        if (preg_match('/^[a-zA-Z][a-zA-Z0-9+.-]*:/', $href) && !preg_match('/^https?:\/\//i', $href)) {
            return null;
        }

        try {
            $base_uri = new Uri($base);
            $href_uri = new Uri($href);
            $resolved = UriResolver::resolve($base_uri, $href_uri);

            if ($resolved->getScheme() === '') {
                $resolved = $resolved->withScheme(self::$base_scheme);
            }
            if ($resolved->getHost() === '') {
                $resolved = $resolved->withHost(self::$base_scheme);
            }

            return (string) $resolved;
        } catch (Throwable) {
            return null;
        }
    }
}
