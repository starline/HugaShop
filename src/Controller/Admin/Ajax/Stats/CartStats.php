<?php

/**
 * HugaShop - Selling anything
 *
 * @author Andri Huga
 * @version 2.3
 *
 */

namespace App\Controller\Admin\Ajax\Stats;

use HugaShop\Services\Request;
use HugaShop\Services\Statistics;
use App\Controller\BaseAdminController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

class CartStats extends BaseAdminController
{
    #[Route('/admin/ajax/stats/cart', name: 'CartStatsAdmin')]
    public function index()
    {

        $this->checkAdminAccess('order', checkCSRF: true);

        $from_date = Request::post('fromDate');
        $to_date   = Request::post('toDate');
        $type      = Request::post('type');

        if (Request::post('filter') === 'byMonth') {
            $result = Statistics::cartsByMonth($from_date, $to_date, $type);
        } else {
            $result = Statistics::cartsByDay($from_date, $to_date, $type);
        }

        return new JsonResponse($result);
    }
}
