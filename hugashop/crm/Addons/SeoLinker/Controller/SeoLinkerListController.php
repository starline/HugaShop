<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.3
 *
 */

namespace HugaShop\Addons\SeoLinker\Controller;

use HugaShop\Services\Config;
use HugaShop\Services\Design;
use HugaShop\Services\Request;
use App\Services\PaginationService;
use App\Controller\BaseAdminController;
use HugaShop\Addons\BaseAddonTrait;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use HugaShop\Addons\SeoLinker\Services\ScanBatch;
use HugaShop\Addons\SeoLinker\Models\SeoLinkerLink;
use HugaShop\Addons\SeoLinker\Models\SeoLinker;

final class SeoLinkerListController extends BaseAdminController
{
    use BaseAddonTrait;

    #[Route('/SeoLinker', name: 'AddonSeoLinker', priority: 20)]
    public function index()
    {
        $this->checkAdminAccess('addon');

        $base_url = $this->getSettings()->base_url ?? rtrim(Config::get('root_url'), '/') . '/';

        if (Request::post('scan')) {
            if (Request::post('start')) {
                SeoLinker::query()->delete();
                SeoLinkerLink::query()->delete();
            }

            [$scanned, $pending] = ScanBatch::scanBatch(
                $base_url,
                limit: 1,
                delay: $this->getSettings()->delay
            );

            if (Request::isAjax()) {
                return new JsonResponse([
                    'scanned' => $scanned,
                    'pending' => $pending,
                ]);
            }
        }

        $filter         = PaginationService::initFilter();
        $pages          = SeoLinker::getList($filter, order: ['in_internal', 'desc']);
        $pages_count    = SeoLinker::getCount($filter);

        Design::assign('pagination',    PaginationService::getPagination($pages_count, $filter));
        Design::assign('pages',         $pages);
        Design::assign('pages_total',   $pages_count);
        Design::assign('addon',     $this->getAddon());

        return $this->fetchAddonResponse('report.tpl');
    }
}
