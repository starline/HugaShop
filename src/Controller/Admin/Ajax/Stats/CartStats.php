<?php

namespace App\Controller\Admin\Ajax\Stats;

use HugaShop\Api\Request;
use HugaShop\Api\Statistics;
use App\Controller\BaseAdminController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

class CartStats extends BaseAdminController
{
    #[Route('/admin/ajax/stats/cart', name: 'CartStatsAdmin')]
    public function index()
    {
        if (!$this->checkAdminAccess('order') || !Request::checkCSRF()) {
            throw $this->createNotFoundException('Access denied CSRF');
        }

        $from_date = Request::post('fromDate') ?: null;
        $to_date = Request::post('toDate') ?: null;
        $type = Request::post('type') ?: null;

        $result = Statistics::cartsByDay($from_date, $to_date, $type);

        return new JsonResponse($result);
    }
}
