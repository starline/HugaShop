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
use HugaShop\Models\Finance\FinancePayment;
use App\Controller\BaseAdminController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

class OrderStats extends BaseAdminController
{
    #[Route('/admin/ajax/stats/order', name: 'OrderStatsAdmin')]
    public function index()
    {

        if (!$this->checkAdminAccess(['stats', 'user']) || !Request::checkCSRF()) { # Check acces
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


        // В день
        //
        if (Request::post('filter', 'string') == 'byDay') {

            // Заказы
            // request_type
            // totalPrice - выручка
            // profitPrice - прибыль
            // amout - кол-во заказов
            $result = Statistics::ordersSum($request_type, 'byDay', $from_date, $to_date);
        }

        // В месяц
        //
        elseif (Request::post('filter')  == 'byMonth') {


            // Для  опред. товара
            if (!empty($product_id = Request::postInt('product_id'))) {

                // Поставка или списание со склада
                // add - поставка
                // delete - списание
                if (in_array($request_type, ['add', 'delete'])) {
                    $result = Statistics::productWarehouseMovemetByMonth($product_id, $request_type);
                }

                // totalPrice - Сумма выручки
                // profitPrice -  Сумма пртбыли
                // amount - Кол-во проданых
                elseif (in_array($request_type, ['totalPrice', 'profitPrice', 'amount'])) {
                    $result = Statistics::productByMonth($product_id, $request_type);
                }
            }


            // Для  опред. категории
            elseif (!empty($category_id = Request::postInt('category_id'))) {

                // totalPrice - Сумма выручки
                // profitPrice -  Сумма прибыли
                // amount - Кол-во проданых
                if (in_array($request_type, ['totalPrice', 'profitPrice', 'amount'])) {
                    $result = Statistics::productsCategoryByMonth($category_id, $request_type);
                }
            }


            // Для опред. менеджера
            elseif (!empty($manager_id = Request::postInt('manager_id'))) {

                // totalPrice - Общая сумма комиссии менеджера
                // amount - кол-во обработаных заказов
                if (in_array($request_type, ['totalPrice', 'amount'])) {
                    $result = Statistics::managerOrdersByMonth($manager_id, $request_type);
                }

                // Платежи менеджеру (траты)
                elseif ($request_type == 'totalPayments') {
                    $user_rel_payments = FinancePayment::getUserPayments($manager_id);

                    $payments_ids = [];
                    foreach ($user_rel_payments as $urp) {
                        $payments_ids[] = $urp->payment_id;
                    }

                    if (!empty($payments_ids)) {
                        $result = Statistics::financeByMonth(['payments_ids' => $payments_ids, 'type' => 'minus']);
                    }
                }
            }


            // Заказы
            // totalPrice - выручка
            // profitPrice - прибыль
            // amout - кол-во заказов
            else {
                $filters = array();
                if (Request::postInt('paymentMethod')) {
                    $filters['payment_method_id'] = Request::postInt('paymentMethod');
                }

                $result = Statistics::ordersSum($request_type, "byMonth", null, null, $filters);
            }
        }

        return new JsonResponse($result);
    }
}
