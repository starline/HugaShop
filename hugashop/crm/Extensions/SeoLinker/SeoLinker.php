<?php

/**
 * SeoLinker extension
 */

namespace HugaShop\Extensions\SeoLinker;

use HugaShop\Models\Config;
use HugaShop\Models\Design;
use HugaShop\Extensions\BaseExtension;
use HugaShop\Extensions\SeoLinker\Models\SeoLinker as SeoLinkerModel;
use HugaShop\Extensions\SeoLinker\Models\SeoLinkerLink as SeoLinkerLinkModel;
use HugaShop\Models\Request;
use Spatie\Crawler\Crawler;
use Spatie\Crawler\CrawlProfiles\CrawlInternalUrls;
use HugaShop\Extensions\SeoLinker\Services\CrawlerObserver;

final class SeoLinker extends BaseExtension
{
    /**
     * Show links report
     */
    public function index()
    {
        $baseUrl = rtrim(Config::get('root_url'), '/') . '/';

        if (Request::post('scan')) {
            if (Request::post('start')) {
                $model = SeoLinkerModel::getModel();
                $model->runWithInitTable(function () use ($model) {
                    $model->newQuery()->delete();
                });
                $linkModel = SeoLinkerLinkModel::getModel();
                $linkModel->runWithInitTable(function () use ($linkModel) {
                    $linkModel->newQuery()->delete();
                });
            }

            [$scanned, $pending] = $this->scanBatch($baseUrl, 10);

            if (Request::isAjax()) {
                return new \Symfony\Component\HttpFoundation\JsonResponse([
                    'scanned' => $scanned,
                    'pending' => $pending,
                ]);
            }
        }

        $pages = SeoLinkerModel::getList(order: ['in_internal', 'desc']);
        $linksMap = [];
        foreach ($pages as $p) {
            $linksMap[$p->url] = SeoLinkerLinkModel::getList(['from_url' => $p->url]);
        }

        Design::assign('pages', $pages);
        Design::assign('links_map', $linksMap);

        return $this->getTemplatePath('templates/report.tpl');
    }

    private function scanBatch(string $baseUrl, int $limit): array
    {
        $model = SeoLinkerModel::getModel();
        $linkModel = SeoLinkerLinkModel::getModel();

        $model->runWithInitTable(fn () => null);
        $linkModel->runWithInitTable(fn () => null);

        if (!$model->newQuery()->where('url', $baseUrl)->exists()) {
            $model->newQuery()->insert([
                'url' => $baseUrl,
                'depth' => 0,
                'scanned' => 0,
            ]);
        }

        $pages = $model->newQuery()->where('scanned', 0)->limit($limit)->get();

        foreach ($pages as $page) {
            [$outInternal, $outExternal, $links] = $this->crawlPage($page->url, $page->depth);

            $model->newQuery()->where('id', $page->id)->update([
                'scanned' => 1,
                'out_internal' => $outInternal,
                'out_external' => $outExternal,
            ]);

            foreach ($links as $ln) {
                $linkModel->create($ln);

                if ($ln['type'] === 'internal') {
                    $target = SeoLinkerModel::getOne(['url' => $ln['to_url']]);
                    if (!$target) {
                        SeoLinkerModel::create([
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
                        SeoLinkerModel::where('url', $ln['to_url'])->increment('in_internal');
                    }
                }
            }
        }

        $scanned = $model->newQuery()->where('scanned', 1)->count();
        $pending = $model->newQuery()->where('scanned', 0)->count();

        return [$scanned, $pending];
    }

    private function crawlPage(string $url, int $depth): array
    {
        $parts = parse_url($url);
        $scheme = $parts['scheme'] ?? 'http';
        $host = $parts['host'] ?? '';

        $observer = new CrawlerObserver($scheme, $host);

        Crawler::create()
            ->setCrawlObserver($observer)
            ->setCrawlProfile(new CrawlInternalUrls($url))
            ->setMaximumDepth(0)
            ->startCrawling($url);

        $res = $observer->results[$url] ?? ['out_internal' => 0, 'out_external' => 0];

        return [
            $res['out_internal'],
            $res['out_external'],
            $observer->links,
        ];
    }
}

