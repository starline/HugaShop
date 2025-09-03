<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.9
 *
 */

namespace HugaShop\Addons\SeoLinker\Services;

use Psr\Http\Message\UriInterface;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\DomCrawler\Crawler;
use GuzzleHttp\Exception\RequestException;
use Spatie\Crawler\CrawlObservers\CrawlObserver;
use HugaShop\Addons\SeoLinker\Services\DomParser\LinksParser;

final class CrawlerObserver extends CrawlObserver
{
    public array $results = [];
    public array $links = [];

    public function __construct(private string $scheme, private string $host) {}


    /**
     * Called when the crawler will crawl the url.
     */
    public function willCrawl(UriInterface $url, ?string $linkText): void {}


    /**
     * Called when the crawler has crawled the given url.
     */
    public function crawled(UriInterface $url, ResponseInterface $response, ?UriInterface $foundOnUrl = null, ?string $linkText = null): void
    {
        if ($response->getStatusCode() !== 200) {
            return;
        }

        $html           = (string) $response->getBody();
        $current_url    = (string) $url;

        $dom = new Crawler($html, (string) $url);


        // Meta tags
        $title_node = $dom->filter('title')->first();
        $title = $title_node->count() ? trim($title_node->text()) : '';

        $description_node = $dom->filterXPath("//meta[translate(@name,'ABCDEFGHIJKLMNOPQRSTUVWXYZ','abcdefghijklmnopqrstuvwxyz')='description']")->first();
        $description = $description_node->count() ? trim($description_node->attr('content')) : '';

        $h1_node = $dom->filter('h1')->first();
        $h1 = $h1_node->count() ? trim($h1_node->text()) : '';


        // Links
        [$out_internal, $out_external, $this->links] = LinksParser::get($dom, $current_url, $this->host, $this->scheme);

        // Result
        $this->results[$current_url] = [
            'url'               => $current_url,
            'out_internal'      => $out_internal,
            'out_external'      => $out_external,
            'meta_title'        => $title,
            'meta_description'  => $description,
            'h1'                => $h1,
        ];
    }


    /**
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


    /**
     * Called when the crawl has ended.
     */
    public function finishedCrawling(): void {}
}
