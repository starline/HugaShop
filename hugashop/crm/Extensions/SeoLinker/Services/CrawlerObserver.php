<?php

namespace HugaShop\Extensions\SeoLinker\Services;

use Spatie\Crawler\CrawlObserver;
use Spatie\Crawler\CrawlUrl;
use Psr\Http\Message\ResponseInterface;

final class CrawlerObserver extends CrawlObserver
{
    public array $results = [];
    public array $links = [];

    public function __construct(private string $scheme, private string $host)
    {
    }

    public function crawled(CrawlUrl $url, ResponseInterface $response, ?CrawlUrl $foundOnUrl = null): void
    {
        $html = (string) $response->getBody();
        $current = (string) $url->url;

        $outInternal = 0;
        $outExternal = 0;

        $dom = new \DOMDocument();
        @($dom->loadHTML($html));
        $xpath = new \DOMXPath($dom);
        $nodes = $xpath->evaluate("//a[@href]");

        foreach ($nodes as $node) {
            $href = trim($node->getAttribute('href'));
            if ($href === '' || str_starts_with($href, '#') || str_starts_with(strtolower($href), 'javascript:') || str_starts_with($href, 'mailto:')) {
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

    private function absoluteUrl(string $href, string $base): ?string
    {
        if (preg_match('/^https?:\/\//i', $href)) {
            return $href;
        }
        if (str_starts_with($href, '//')) {
            return $this->scheme . ':' . $href;
        }
        if (str_starts_with($href, '/')) {
            return $this->scheme . '://' . $this->host . $href;
        }
        if ($href === '') {
            return null;
        }
        $path = parse_url($base, PHP_URL_PATH) ?: '/';
        if (!str_ends_with($path, '/')) {
            $path = dirname($path) . '/';
        }
        return $this->scheme . '://' . $this->host . $path . $href;
    }
}
