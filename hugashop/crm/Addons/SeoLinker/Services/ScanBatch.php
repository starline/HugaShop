<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.9
 * 
 * SeoLinker addon
 * @link https://github.com/spatie/crawler
 *
 */

namespace HugaShop\Addons\SeoLinker\Services;

use Spatie\Crawler\Crawler;
use HugaShop\Addons\SeoLinker\Models\SeoLinker;
use Spatie\Crawler\CrawlProfiles\CrawlInternalUrls;
use HugaShop\Addons\SeoLinker\Models\SeoLinkerLink;
use HugaShop\Addons\SeoLinker\Services\CrawlerObserver;


final class ScanBatch
{

    private static int $delay = 0;
    private static int $limit = 1;
    private static int $depth = 0;

    /**
     * Scan Batch
     */
    public static function scanBatch(string $base_url, int $limit = 1, int $delay = 0, int $depth = 0): array
    {

        // Params
        self::$delay = max(0, $delay);
        self::$limit = max(1, $limit);
        self::$depth = max(0, $depth);

        SeoLinker::firstOrCreate(
            ['url' => $base_url],
            ['depth' => 0, 'scanned' => 0]
        );

        $pages = SeoLinker::where('scanned', 0)->limit(self::$limit)->get();
        foreach ($pages as $page) {
            [$out_internal, $out_external, $meta_title, $meta_description, $h1, $links] = self::crawlPage($page->url);

            $page->update([
                'scanned'           => 1,
                'out_internal'      => $out_internal,
                'out_external'      => $out_external,
                'meta_title'        => $meta_title,
                'meta_description'  => $meta_description,
                'h1'                => $h1,
            ]);

            foreach ($links as $ln) {
                $exists = SeoLinkerLink::getOne([
                    'from_url'  => $ln['from_url'],
                    'to_url'    => $ln['to_url'],
                    'type'      => $ln['type']
                ]);

                if (!$exists) {
                    SeoLinkerLink::createOne($ln);
                }

                if ($ln['type'] === 'image') {
                    continue;
                }

                // Add internal links to scan line. Except nofollow
                if ($ln['type'] === 'internal' && !$ln['nofollow']) {
                    $target = SeoLinker::getOne(['url' => $ln['to_url']]);
                    if (!$target) {
                        SeoLinker::createOne([
                            'url'           => $ln['to_url'],
                            'depth'         => $page->depth + 1,
                            'scanned'       => 0,
                            'in_internal'   => 1,
                        ]);
                    } else {
                        if ($target->depth > $page->depth + 1) {
                            $target->depth = $page->depth + 1;
                            $target->save();
                        }
                        SeoLinker::where('url', $ln['to_url'])->increment('in_internal');
                    }
                }
            }
        }

        $scanned = SeoLinker::where('scanned', 1)->count();
        $pending = SeoLinker::where('scanned', 0)->count();

        return [$scanned, $pending];
    }


    /**
     * Crawl
     */
    private static function crawlPage(string $url): array
    {
        $parts      = parse_url($url);
        $scheme     = $parts['scheme'] ?? 'http';
        $host       = $parts['host'] ?? '';
        $base_url   = $scheme . '://' . $host;

        $observer = new CrawlerObserver($scheme, $host);

        Crawler::create()
            ->setCrawlObserver($observer)
            //->ignoreRobots() # ignore robots.txt rules
            //->acceptNofollowLinks()
            ->setDelayBetweenRequests(self::$delay) // After every page crawled, the crawler will wait for 150ms
            ->setCrawlProfile(new CrawlInternalUrls($base_url))
            ->setMaximumDepth(self::$depth)
            ->setParseableMimeTypes(['text/html'])
            ->startCrawling($base_url);

        $res = $observer->results[$base_url] ?? [
            'out_internal'      => 0,
            'out_external'      => 0,
            'meta_title'        => '',
            'meta_description'  => '',
            'h1'                => '',
        ];

        return [
            $res['out_internal'],
            $res['out_external'],
            $res['meta_title'],
            $res['meta_description'],
            $res['h1'],
            $observer->links
        ];
    }
}
