<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.1
 */

namespace HugaShop\Addons\ProductSearchKeyword\Controller;

use HugaShop\Services\Design;
use App\Services\PaginationService;
use App\Controller\BaseAdminController;
use HugaShop\Addons\BaseAddonTrait;
use Symfony\Component\Routing\Attribute\Route;
use HugaShop\Addons\ProductSearchKeyword\Models\ProductSearchKeyword;

final class ProductSearchKeywordListController extends BaseAdminController
{
    use BaseAddonTrait;

    #[Route('/ProductSearchKeyword', name: 'AddonProductSearchKeywordList', priority: 20)]
    public function index()
    {
        $this->checkAdminAccess('addon');

        $filter         = PaginationService::initFilter();
        $keywords       = ProductSearchKeyword::getList($filter, order: ['created_at', 'desc']);
        $keywords_count = ProductSearchKeyword::getCount($filter);

        Design::assign('keywords',    $keywords);
        Design::assign('pagination',  PaginationService::getPagination($keywords_count, $filter));
        Design::assign('addon',       $this->getAddon());
        Design::assign('meta_title',  'Поисковые запросы');

        return $this->fetchAddonResponse('list.tpl');
    }
}
