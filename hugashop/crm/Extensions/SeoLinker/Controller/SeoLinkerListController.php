<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.2
 *
 */

namespace HugaShop\Extensions\SeoLinker\Controller;

use HugaShop\Services\Config;
use HugaShop\Services\Design;
use HugaShop\Services\Request;
use App\Services\PaginationService;
use App\Controller\BaseAdminController;
use HugaShop\Extensions\BaseExtensionTrait;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use HugaShop\Extensions\SeoLinker\Services\ScanBatch;
use HugaShop\Extensions\SeoLinker\Models\SeoLinkerLink;
use HugaShop\Extensions\SeoLinker\Models\SeoLinker;

final class SeoLinkerListController extends BaseAdminController
{
    use BaseExtensionTrait;

    #[Route('/SeoLinker', name: 'ExtSeoLinker', priority: 20)]
    public function index()
    {
        $this->checkAdminAccess('extension');

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
        $pages_count    = SeoLinker::getCount();

        Design::assign('pagination',    PaginationService::getPagination($pages_count, $filter));
        Design::assign('pages',         $pages);
        Design::assign('pages_total',   $pages_count);
        Design::assign('extension',     $this->getExtension());

        return $this->fetchExtResponse('report.tpl');
    }
}
