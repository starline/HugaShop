<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.1
 *
 */

namespace HugaShop\Extensions\SeoLinker\Services;

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


    /*
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

        foreach ($nodes as $node) {
            $href = trim($node->getAttribute('href'));
            if (
                $href === '' ||
                str_starts_with($href, '#') ||
                str_starts_with(strtolower($href), 'javascript:') ||
                str_starts_with($href, 'mailto:') ||
                str_starts_with($href, 'tel:')
            ) {
                continue;
            }

            $abs = $this->absoluteUrl($href, $current);
            if (!$abs) {
                continue;
            }

            $p = parse_url($abs);
            if (($p['host'] ?? '') === $this->host) {
                $outInternal++;
                $this->links[] = [
                    'from_url' => $current,
                    'to_url'   => $abs,
                    'type'     => 'internal',
                ];
            } else {
                $outExternal++;
                $this->links[] = [
                    'from_url' => $current,
                    'to_url'   => $abs,
                    'type'     => 'external',
                ];
            }
        }

        $this->results[$current] = [
            'url' => $current,
            'out_internal' => $outInternal,
            'out_external' => $outExternal,
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
