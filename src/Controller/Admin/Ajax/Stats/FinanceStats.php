<?php

/**
 * HugaShop - Selling anything
 *
 * @author Andri Huga
 * @version 2.4
 *
 */

namespace App\Controller\Admin\Ajax\Stats;

use HugaShop\Services\Request;
use HugaShop\Services\Statistics;
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

        $request_type   = Request::post('type', 'string');
        $from_date      = Request::post('fromDate') ?: null;
        $to_date        = Request::post('toDate') ?: null;
        $category_id    = Request::postInt('category_id') ?: null;

        // В месяц
        if (Request::post('filter', 'string')  === 'byMonth') {

            // Для опред. кошелка
            // plus - приход
            // minus - расход
            $params = ["type" => $request_type, "category_id" => $category_id];

            if ($from_date) {
                $params['fromDate'] = $from_date;
            }
            if ($to_date) {
                $params['toDate'] = $to_date;
            }

            if (!empty($purse_id = Request::postInt('purse_id'))) {
                $params['purse_id'] = $purse_id;
                $result = Statistics::financeByMonth($params);
            }


            // Общий финансовый график.
            // Убираем из дaнных переводы между кошельками related_payment_id = "NULL"
            // plus - приход
            // minus - расход
            else {
                $params['related_payment_id'] = "NULL";
                $result = Statistics::financeByMonth($params);
            }
        }

        return new JsonResponse($result);
    }
}
