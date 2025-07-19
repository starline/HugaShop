<?php
/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.0
 */

namespace HugaShop\Extensions\ProductSearchKeyword\Controller;

use HugaShop\Services\Design;
use App\Services\PaginationService;
use App\Controller\BaseAdminController;
use HugaShop\Extensions\BaseExtensionTrait;
use Symfony\Component\Routing\Attribute\Route;
use HugaShop\Extensions\ProductSearchKeyword\Models\ProductSearchKeyword;

final class ProductSearchKeywordListController extends BaseAdminController
{
    use BaseExtensionTrait;

    #[Route('/ProductSearchKeyword', name: 'ExtProductSearchKeywordList', priority: 20)]
    public function index()
    {
        $this->checkAdminAccess('extension');

        $filter         = PaginationService::initFilter();
        $keywords       = ProductSearchKeyword::getList($filter, order: ['created_at', 'desc']);
        $keywords_count = ProductSearchKeyword::getCount($filter);

        Design::assign('keywords',    $keywords);
        Design::assign('pagination',  PaginationService::getPagination($keywords_count, $filter));
        Design::assign('extension',   $this->getExtension());
        Design::assign('meta_title',  'Поисковые запросы');

        return $this->fetchExtResponse('list.tpl');
    }
}
