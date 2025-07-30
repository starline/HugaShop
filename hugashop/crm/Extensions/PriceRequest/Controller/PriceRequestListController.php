<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.0
 */

namespace HugaShop\Extensions\PriceRequest\Controller;

use HugaShop\Services\Design;
use HugaShop\Services\Request;
use App\Services\PaginationService;
use App\Controller\BaseAdminController;
use HugaShop\Extensions\BaseExtensionTrait;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use HugaShop\Extensions\PriceRequest\Models\PriceRequest;

final class PriceRequestListController extends BaseAdminController
{
    use BaseExtensionTrait;

    #[Route('/PriceRequest', name: 'ExtPriceRequestList', priority: 20)]
    public function index(): Response
    {
        $this->checkAdminAccess('extension');

        if (Request::checkCSRF()) {
            $ids = Request::post('check');
            if (is_array($ids)) {
                if (Request::post('action') === 'delete') {
                    PriceRequest::deleteOne($ids);
                }
            }
        }

        $filter = PaginationService::initFilter();
        $requests = PriceRequest::getList($filter, order: ['created_at', 'desc'], join: ['product']);
        $requests_count = PriceRequest::getCount($filter);

        Design::assign('requests', $requests);
        Design::assign('pagination', PaginationService::getPagination($requests_count, $filter));
        Design::assign('extension', $this->getExtension());
        Design::assign('meta_title', 'Запросы на скидку');

        return $this->fetchExtResponse('list.tpl');
    }
}
