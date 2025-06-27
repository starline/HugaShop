<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.3
 * 
 * SeoLinker extension
 * @link https://github.com/spatie/crawler
 *
 */

namespace HugaShop\Extensions\SeoLinker;

use HugaShop\Models\Config;
use HugaShop\Models\Design;
use HugaShop\Models\Request;
use HugaShop\Models\Settings;
use HugaShop\Extensions\BaseExtension;
use Symfony\Component\HttpFoundation\JsonResponse;
use HugaShop\Extensions\SeoLinker\Services\ScanBatch;
use HugaShop\Extensions\SeoLinker\Models\SeoLinker as SeoLinkerModel;
use HugaShop\Extensions\SeoLinker\Models\SeoLinkerLink;

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
                SeoLinkerModel::query()->delete();
                SeoLinkerLink::query()->delete();
            }

            [$scanned, $pending] = ScanBatch::scanBatch($base_url, limit: 1);

            if (Request::isAjax()) {
                return new JsonResponse([
                    'scanned' => $scanned,
                    'pending' => $pending,
                ]);
            }
        }

        $filter = [];
        $filter['page'] = max(1, Request::get('page', 'int'));
        $filter['limit'] = Request::get('page', 'string') == 'all' ? 'all' : Settings::getParam('products_num_admin');

        $pages          = SeoLinkerModel::getList($filter, order: ['in_internal', 'desc']);
        $pages_count    = SeoLinkerModel::getCount();

        Design::assign('pages', $pages);
        Design::assign('pages_total', $pages_count);
        Design::assign('pages_count', ceil($pages_count / Settings::getParam('products_num_admin')));
        Design::assign('current_page', $filter['limit'] == 'all' ? 'all' : $filter['page']);

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

        Design::assign('page', $page);
        Design::assign('links', $links);

        return $this->getTemplatePath('templates/page.tpl');
    }
}
