<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.2
 */

namespace HugaShop\Addons\ProductPriceRequest\Controller;

use HugaShop\Services\Design;
use HugaShop\Services\Secure;
use HugaShop\Services\Request;
use App\Services\PaginationService;
use App\Controller\BaseAdminController;
use HugaShop\Addons\BaseAddonTrait;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use HugaShop\Addons\ProductPriceRequest\Models\PriceRequest;

final class PriceRequestListController extends BaseAdminController
{
    use BaseAddonTrait;

    #[Route('/ProductPriceRequest', name: 'ExtPriceRequestList', priority: 20)]
    public function index(): Response
    {
        $this->checkAdminAccess('addon');

        if (Secure::checkCSRF()) {
            $ids = Request::post('check');
            if (is_array($ids)) {
                if (Request::post('action') === 'delete') {
                    PriceRequest::deleteOne($ids);
                }
            }
        }

        $filter         = PaginationService::initFilter();
        $requests       = PriceRequest::getList($filter, order: ['created_at', 'desc'], join: ['product']);
        $requests_count = PriceRequest::getCount($filter);

        Design::assign('requests', $requests);
        Design::assign('pagination', PaginationService::getPagination($requests_count, $filter));
        Design::assign('addon', $this->getAddon());

        return $this->fetchAddonResponse('list.tpl');
    }
}
