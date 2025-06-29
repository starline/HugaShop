<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.5
 * 
 * SeoLinker extension
 * @link https://github.com/spatie/crawler
 *
 */

namespace HugaShop\Extensions\SeoLinker;

use HugaShop\Models\Config;
use HugaShop\Models\Design;
use HugaShop\Models\Request;
use App\Services\PaginationService;
use HugaShop\Extensions\BaseExtension;
use Symfony\Component\HttpFoundation\JsonResponse;
use HugaShop\Extensions\SeoLinker\Services\ScanBatch;
use HugaShop\Extensions\SeoLinker\Models\SeoLinkerLink;
use HugaShop\Extensions\SeoLinker\Models\SeoLinker as SeoLinkerModel;

final class SeoLinker extends BaseExtension
{

    /**
     * Show links report
     */
    public function index()
    {

        $base_url = $this->getSetting('base_url') ?? rtrim(Config::get('root_url'), '/') . '/';

        if (Request::post('scan')) {

            if (Request::post('start')) {
                SeoLinkerModel::query()->delete();
                SeoLinkerLink::query()->delete();
            }

            [$scanned, $pending] = ScanBatch::scanBatch(
                $base_url,
                limit: 1,
                delay: $this->getSetting('delay')
            );

            if (Request::isAjax()) {
                return new JsonResponse([
                    'scanned' => $scanned,
                    'pending' => $pending,
                ]);
            }
        }

        $filter = PaginationService::initFilter();

        $pages          = SeoLinkerModel::getList($filter, order: ['in_internal', 'desc']);
        $pages_count    = SeoLinkerModel::getCount();

        Design::assign('pagination', PaginationService::getPagination($pages_count, $filter));
        Design::assign('pages', $pages);
        Design::assign('pages_total', $pages_count);

        return $this->getTemplatePath('templates/report.tpl');
    }


    /** 
     * Page view
     */
    public function page(?int $id = null)
    {
        if (empty($id)) {
            Request::makeRedirect('/admin/extension/SeoLinker');
        }

        $page = SeoLinkerModel::getOne($id);
        if (empty($page)) {
            Request::makeRedirect('/admin/extension/SeoLinker');
        }

        $links = SeoLinkerLink::getList(['from_url' => $page->url]);

        $links_in = SeoLinkerLink::getList([
            'to_url' => $page->url,
            'type'   => 'internal',
        ]);

        foreach ($links_in as $ln) {
            $src = SeoLinkerModel::getOne(['url' => $ln->from_url]);
            $ln->from_id = $src->id ?? null;
        }

        Design::assign('page', $page);
        Design::assign('links', $links);
        Design::assign('links_in', $links_in);

        return $this->getTemplatePath('templates/page.tpl');
    }
}
