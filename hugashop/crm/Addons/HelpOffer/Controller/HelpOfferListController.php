<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.3
 */

namespace HugaShop\Addons\HelpOffer\Controller;

use HugaShop\Services\Design;
use HugaShop\Services\Secure;
use HugaShop\Services\Request;
use App\Services\PaginationService;
use App\Controller\BaseAdminController;
use HugaShop\Addons\BaseAddonTrait;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use HugaShop\Addons\HelpOffer\Models\HelpOffer;

final class HelpOfferListController extends BaseAdminController
{
    use BaseAddonTrait;

    #[Route('/HelpOffer', name: 'AddonHelpOfferList', priority: 20)]
    public function index(): Response
    {
        $this->checkAdminAccess('addon');

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
        Design::assign('requests_count', $requests_count);
        Design::assign('pagination', PaginationService::getPagination($requests_count, $filter));
        Design::assign('addon', $this->getAddon());

        return $this->fetchAddonResponse('list.tpl');
    }
}
