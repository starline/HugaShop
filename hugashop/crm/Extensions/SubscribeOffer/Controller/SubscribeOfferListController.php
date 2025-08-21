<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.1
 */

namespace HugaShop\Extensions\SubscribeOffer\Controller;

use HugaShop\Services\Design;
use HugaShop\Services\Secure;
use HugaShop\Services\Request;
use App\Services\PaginationService;
use App\Controller\BaseAdminController;
use HugaShop\Extensions\BaseExtensionTrait;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use HugaShop\Extensions\SubscribeOffer\Models\SubscribeOffer;

final class SubscribeOfferListController extends BaseAdminController
{
    use BaseExtensionTrait;

    #[Route('/SubscribeOffer', name: 'ExtSubscribeOfferList', priority: 20)]
    public function index(): Response
    {
        $this->checkAdminAccess('extension');

        if (Secure::checkCSRF()) {
            $ids = Request::post('check');
            if (is_array($ids)) {
                if (Request::post('action') === 'delete') {
                    SubscribeOffer::deleteOne($ids);
                }
            }
        }

        $filter         = PaginationService::initFilter();
        $requests       = SubscribeOffer::getList($filter, order: ['created_at', 'desc']);
        $requests_count = SubscribeOffer::getCount($filter);

        Design::assign('requests', $requests);
        Design::assign('requests_count', $requests_count);
        Design::assign('pagination', PaginationService::getPagination($requests_count, $filter));
        Design::assign('extension', $this->getExtension());

        return $this->fetchExtResponse('list.tpl');
    }
}
