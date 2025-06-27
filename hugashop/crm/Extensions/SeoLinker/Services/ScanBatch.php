<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.4
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
        $model          = SeoLinker::getModel();
        $linkModel      = SeoLinkerLink::getModel();

        $model->runWithInitTable(fn() => null);
        $linkModel->runWithInitTable(fn() => null);

        if (!$model->newQuery()->where('url', $base_url)->exists()) {
            $model->newQuery()->insert([
                'url' => $base_url,
                'depth' => 0,
                'scanned' => 0,
            ]);
        }

        $pages = $model->newQuery()->where('scanned', 0)->limit($limit)->get();

        foreach ($pages as $page) {
            [$outInternal, $outExternal, $links] = self::crawlPage($page->url);

            $model->newQuery()->where('id', $page->id)->update([
                'scanned' => 1,
                'out_internal' => $outInternal,
                'out_external' => $outExternal,
            ]);

            foreach ($links as $ln) {
                $exists = $linkModel->newQuery()
                    ->where('from_url', $ln['from_url'])
                    ->where('to_url', $ln['to_url'])
                    ->where('type', $ln['type'])
                    ->exists();
                if (!$exists) {
                    $linkModel->create($ln);
                }

                if ($ln['type'] === 'image') {
                    continue;
                }

                if ($ln['type'] === 'internal') {
                    $target = SeoLinker::getOne(['url' => $ln['to_url']]);
                    if (!$target) {
                        SeoLinker::create([
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

        $scanned = $model->newQuery()->where('scanned', 1)->count();
        $pending = $model->newQuery()->where('scanned', 0)->count();

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
