<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.5
 *
 */

namespace HugaShop\Extensions\SeoLinker\Services;

use Spatie\Crawler\Crawler;
use HugaShop\Extensions\SeoLinker\Models\SeoLinker;
use Spatie\Crawler\CrawlProfiles\CrawlInternalUrls;
use HugaShop\Extensions\SeoLinker\Models\SeoLinkerLink;
use HugaShop\Extensions\SeoLinker\Services\CrawlerObserver;


final class ScanBatch
{

    /**
     * Scan Batch
     */
    public static function scanBatch(string $base_url, int $limit): array
    {

        if (!SeoLinker::where('url', $base_url)->exists()) {
            SeoLinker::insert([
                'url' => $base_url,
                'depth' => 0,
                'scanned' => 0,
            ]);
        }

        $pages = SeoLinker::where('scanned', 0)->limit($limit)->get();

        foreach ($pages as $page) {
            [$outInternal, $outExternal, $links] = self::crawlPage($page->url);

            SeoLinker::where('id', $page->id)->update([
                'scanned' => 1,
                'out_internal' => $outInternal,
                'out_external' => $outExternal,
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
                            'url' => $ln['to_url'],
                            'depth' => $page->depth + 1,
                            'scanned' => 0,
                            'in_internal' => 1,
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

        $observer = new CrawlerObserver($scheme, $host);

        Crawler::create()
            ->setCrawlObserver($observer)
            //->ignoreRobots() # ignore robots.txt rules
            //->acceptNofollowLinks()
            ->setCrawlProfile(new CrawlInternalUrls($url))
            ->setMaximumDepth(0)
            ->setParseableMimeTypes(['text/html'])
            ->startCrawling($url);

        $res = $observer->results[$url] ?? ['out_internal' => 0, 'out_external' => 0];

        return [
            $res['out_internal'],
            $res['out_external'],
            $observer->links
        ];
    }
}
