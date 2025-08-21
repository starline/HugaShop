<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.1
 */

namespace HugaShop\Extensions\HelpOffer\Controller;

use HugaShop\Services\Design;
use HugaShop\Services\Secure;
use HugaShop\Services\Request;
use App\Services\PaginationService;
use App\Controller\BaseAdminController;
use HugaShop\Extensions\BaseExtensionTrait;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use HugaShop\Extensions\HelpOffer\Models\HelpOffer;

final class HelpOfferListController extends BaseAdminController
{
    use BaseExtensionTrait;

    #[Route('/HelpOffer', name: 'ExtHelpOfferList', priority: 20)]
    public function index(): Response
    {
        $this->checkAdminAccess('extension');

        if (Secure::checkCSRF()) {
            $ids = Request::post('check');
            if (is_array($ids)) {
                if (Request::post('action') === 'delete') {
                    HelpOffer::deleteOne($ids);
                }
            }
        }

        $filter         = PaginationService::initFilter();
        $requests       = HelpOffer::getList($filter, order: ['created_at', 'desc']);
        $requests_count = HelpOffer::getCount($filter);

        Design::assign('requests', $requests);
        Design::assign('pagination', PaginationService::getPagination($requests_count, $filter));
        Design::assign('extension', $this->getExtension());

        return $this->fetchExtResponse('list.tpl');
    }
}
