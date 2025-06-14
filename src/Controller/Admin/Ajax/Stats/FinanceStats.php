<?php

namespace App\Controller\Admin\Ajax\Stats;

use HugaShop\Api\Request;
use HugaShop\Api\Statistics;
use App\Controller\BaseAdminController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

class FinanceStats extends BaseAdminController
{
    #[Route('/admin/ajax/stats/finance', name: 'FinanceStatsAdmin')]
    public function index()
    {

        if (!$this->checkAdminAccess('stats') || !Request::checkCSRF()) { # Check acces
            throw $this->createNotFoundException('Access denied CSRF'); # 404
        }


        $result = null;
        $request_type = Request::post('type', 'string');

        if (!$from_date = Request::post('fromDate')) {
            $from_date = null;
        }

        if (!$to_date = Request::post('toDate')) {
            $to_date = null;
        }


        // В месяц
        //
        if (Request::post('filter', 'string')  == 'byMonth') {

            $category_id = null;
            if (!empty(Request::post('category_id', 'integer'))) {
                $category_id = Request::post('category_id', 'integer');
            }

            // Для опред. кошелка
            // plus - приход
            // minus - расход
            if (!empty($purse_id = Request::post('purse_id', 'integer'))) {
                $result = Statistics::financeByMonth(["purse_id" => $purse_id, "type" => $request_type, "category_id" => $category_id]);
            }


            // Общий финансовый график.
            // Убираем из дaнных переводы между кошельками related_payment_id = "NULL"
            // plus - приход
            // minus - расход
            else {
                $result = Statistics::financeByMonth(["related_payment_id" => "NULL", "type" => $request_type, "category_id" => $category_id]);
            }
        }

        return new JsonResponse($result);
    }
}
