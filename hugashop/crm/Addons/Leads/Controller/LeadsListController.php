<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.1
 *
 * Admin controller for listing leads.
 */

namespace HugaShop\Addons\Leads\Controller;

use HugaShop\Services\Design;
use App\Services\PaginationService;
use App\Controller\BaseAdminController;
use HugaShop\Addons\Leads\Models\Lead;
use HugaShop\Addons\BaseAddonTrait;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class LeadsListController extends BaseAdminController
{
    use BaseAddonTrait;

    #[Route('/Leads', name: 'ExtLeadsList', priority: 20)]
    public function index(): Response
    {
        $filter = PaginationService::initFilter();

        $query = Lead::with(['client'])
            ->withMax('calls as last_call_at', 'created_at')
            ->orderByDesc('last_call_at');

        if (($limit = $filter['limit']) !== null && $limit !== 'all') {
            $query->offset(($filter['page'] - 1) * $limit)->limit($limit);
        }

        $leads = $query->get();
        $leads_count = Lead::getCount();

        Design::assign('pagination', PaginationService::getPagination($leads_count, $filter));
        Design::assign('leads', $leads);
        Design::assign('leads_count', $leads_count);
        Design::assign('addon', $this->getAddon());

        return $this->fetchAddonResponse('leads_list.tpl');
    }
}
