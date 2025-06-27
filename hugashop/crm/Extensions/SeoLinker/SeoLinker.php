<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.1
 * 
 * SeoLinker extension
 * @link https://github.com/spatie/crawler
 *
 */

namespace HugaShop\Extensions\SeoLinker;

use HugaShop\Models\Config;
use HugaShop\Models\Design;
use HugaShop\Models\Request;
use HugaShop\Extensions\BaseExtension;
use Symfony\Component\HttpFoundation\JsonResponse;
use HugaShop\Extensions\SeoLinker\Services\ScanBatch;
use HugaShop\Extensions\SeoLinker\Models\SeoLinker as SeoLinkerModel;
use HugaShop\Extensions\SeoLinker\Models\SeoLinkerLink as SeoLinkerLinkModel;

final class SeoLinker extends BaseExtension
{

    /**
     * Show links report
     */
    public function index()
    {

        $base_url = $this->getConfig('base_url') ?? rtrim(Config::get('root_url'), '/') . '/';

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

            [$scanned, $pending] = ScanBatch::scanBatch($base_url, 1);

            if (Request::isAjax()) {
                return new JsonResponse([
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


    public function page(?int $id = null) {}
}
