<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.7
 *
 */

namespace HugaShop\Addons\SeoLinker\Services;

use Throwable;
use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\Psr7\UriResolver;
use Psr\Http\Message\UriInterface;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Exception\RequestException;
use Spatie\Crawler\CrawlObservers\CrawlObserver;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;

final class CrawlerObserver extends CrawlObserver
{
    public array $results = [];
    public array $links = [];

    public function __construct(private string $scheme, private string $host) {}


    /**
     * Called when the crawler will crawl the url.
     */
    public function willCrawl(UriInterface $url, ?string $linkText): void {}


    public function crawled(UriInterface $url, ResponseInterface $response, ?UriInterface $foundOnUrl = null, ?string $linkText = null): void
    {
        if ($response->getStatusCode() !== 200) {
            return;
        }

        $html       = (string) $response->getBody();
        $current    = (string) $url;

        $dom = new DomCrawler($html, (string) $url);

        $title = '';
        $description = '';
        $h1 = '';

        // Meta tags
        $title_node = $dom->filter('title');
        if ($title_node->count() > 0) {
            $title = trim($title_node->text());
        }

        $description_node = $dom->filterXPath("//meta[translate(@name,'ABCDEFGHIJKLMNOPQRSTUVWXYZ','abcdefghijklmnopqrstuvwxyz')='description']");
        if ($description_node->count() > 0) {
            $description = trim((string) $description_node->first()->attr('content'));
        }

        $h1_node = $dom->filter('h1');
        if ($h1_node->count() > 0) {
            $h1 = trim($h1_node->first()->text());
        }


        // Links
        $outInternal = 0;
        $outExternal = 0;
        $dom->filter('a[href]')->each(function ($node) use (&$outInternal, &$outExternal,  $current) {

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
            $abs = $this->absoluteUrl($href, $current);
            if (!$abs || !is_string($abs)) {
                return;
            }

            // Картинки по расширению
            $path       = (string) (parse_url($abs, PHP_URL_PATH) ?? '');
            $ext        = strtolower((string) pathinfo($path, PATHINFO_EXTENSION));
            $is_image   = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp', 'tif', 'tiff'], true);

            if ($is_image) {
                $this->links[] = [
                    'from_url' => $current,
                    'to_url'   => $abs,
                    'type'     => 'image',
                    'nofollow' => (int) $nofollow,
                ];
                return;
            }

            // Хост/схема
            $p       = parse_url($abs) ?: [];
            $host    = strtolower((string) ($p['host'] ?? ''));
            $scheme  = strtolower((string) ($p['scheme'] ?? '')); # может быть пустым после absoluteUrl, но обычно уже нормализован

            // Нормализуем эталонные хост/схему (свойства класса заданы заранее)
            $base_host   = strtolower((string) $this->host);
            $base_scheme = strtolower((string) $this->scheme);

            // Внутренняя/внешняя
            if ($host === $base_host && ($scheme === '' || $scheme === $base_scheme)) {
                $outInternal++;
                $this->links[] = [
                    'from_url' => $current,
                    'to_url'   => $abs,
                    'type'     => 'internal',
                    'nofollow' => (int) $nofollow,
                ];
            } else {
                $outExternal++;
                $this->links[] = [
                    'from_url' => $current,
                    'to_url'   => $abs,
                    'type'     => 'external',
                    'nofollow' => (int) $nofollow,
                ];
            }
        });

        // Result
        $this->results[$current] = [
            'url'               => $current,
            'out_internal'      => $outInternal,
            'out_external'      => $outExternal,
            'meta_title'        => $title,
            'meta_description'  => $description,
            'h1'                => $h1,
        ];
    }


    /*
     * Called when the crawler had a problem crawling the given url.
     */
    public function crawlFailed(
        UriInterface $url,
        RequestException $requestException,
        ?UriInterface $foundOnUrl = null,
        ?string $linkText = null,
    ): void {

        dd($requestException);
    }


    /*
     * Called when the crawl has ended.
     */
    public function finishedCrawling(): void {}


    /**
     * Make absolute url
     */
    private function absoluteUrl(string $href, string $base): ?string
    {
        if (preg_match('/^https?:\/\//i', $href)) {
            return $href;
        }
        if (str_starts_with($href, '//')) {
            return $this->scheme . ':' . $href;
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
                $resolved = $resolved->withScheme($this->scheme);
            }
            if ($resolved->getHost() === '') {
                $resolved = $resolved->withHost($this->host);
            }

            return (string) $resolved;
        } catch (Throwable) {
            return null;
        }
    }
}
