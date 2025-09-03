<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.6
 *
 */

namespace HugaShop\Addons\SeoLinker\Services;

use DOMXPath;
use Throwable;
use DOMDocument;
use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\Psr7\UriResolver;
use Psr\Http\Message\UriInterface;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Exception\RequestException;
use Spatie\Crawler\CrawlObservers\CrawlObserver;

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

        $outInternal = 0;
        $outExternal = 0;

        $dom = new DOMDocument();
        @($dom->loadHTML($html));
        $xpath = new DOMXPath($dom);
        $nodes = $xpath->evaluate("//a[@href]");

        $metaTitle = trim((string) $xpath->evaluate('string(//title)'));
        $metaNode = $xpath->query("//meta[translate(@name,'ABCDEFGHIJKLMNOPQRSTUVWXYZ','abcdefghijklmnopqrstuvwxyz')='description']");
        $metaDescription = '';
        if ($metaNode->length > 0) {
            $metaDescription = trim((string) $metaNode->item(0)?->getAttribute('content'));
        }
        $h1Node = $xpath->query('//h1');
        $h1 = '';
        if ($h1Node->length > 0) {
            $h1 = trim((string) $h1Node->item(0)?->textContent);
        }

        foreach ($nodes as $node) {
            $href = trim($node->getAttribute('href'));
            $rel  = strtolower($node->getAttribute('rel'));

            $nofollow = str_contains($rel, 'nofollow');

            // Пропускаем ссылки 
            if (
                $href === '' ||
                str_starts_with($href, '#') ||
                str_starts_with(strtolower($href), 'javascript:') ||
                str_starts_with($href, 'mailto:') ||
                str_starts_with($href, 'tel:') ||
                str_starts_with(strtolower($href), 'tg:')
            ) {
                continue;
            }

            $abs = $this->absoluteUrl($href, $current);
            if (!$abs) {
                continue;
            }


            // Image
            $path = parse_url($abs, PHP_URL_PATH) ?? '';
            $ext  = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp', 'tiff']);

            $p = parse_url($abs);
            if ($isImage) {
                $this->links[] = [
                    'from_url' => $current,
                    'to_url'   => $abs,
                    'type'     => 'image',
                    'nofollow' => (int) $nofollow,
                ];
                continue;
            }


            $scheme = strtolower($p['scheme'] ?? $this->scheme);

            // Internal links
            if (($p['host'] ?? '') === $this->host && $scheme === strtolower($this->scheme)) {
                $outInternal++;
                $this->links[] = [
                    'from_url' => $current,
                    'to_url'   => $abs,
                    'type'     => 'internal',
                    'nofollow' => (int) $nofollow,
                ];
            }

            // External links
            else {
                $outExternal++;
                $this->links[] = [
                    'from_url' => $current,
                    'to_url'   => $abs,
                    'type'     => 'external',
                    'nofollow' => (int) $nofollow,
                ];
            }
        }

        // Result
        $this->results[$current] = [
            'url' => $current,
            'out_internal' => $outInternal,
            'out_external' => $outExternal,
            'meta_title' => $metaTitle,
            'meta_description' => $metaDescription,
            'h1' => $h1,
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
            $baseUri = new Uri($base);
            $hrefUri = new Uri($href);
            $resolved = UriResolver::resolve($baseUri, $hrefUri);

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
