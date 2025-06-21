<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.0
 *
 */

namespace App\Controller\Admin\Order;

use HugaShop\Models\Order\Order;
use HugaShop\Models\Design;
use HugaShop\Models\Helper;
use HugaShop\Models\Request;
use HugaShop\Models\Order\OrderPayment;
use App\Controller\BaseAdminController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PaymentListController extends BaseAdminController
{
    #[Route('/admin/order/payments', name: 'OrderPaymentListAdmin')]
    public function index(): Response
    {

        $this->checkAdminAccess('order_payment');

        // Обработка действий
        if (Request::checkCSRF()) {

            // Действия с выбранными
            $ids = Request::post('check');
            if (is_array($ids)) {
                switch (Request::post('action')) {
                    case 'disable': {
                            OrderPayment::updateOne($ids, ['enabled' => 0]);
                            break;
                        }
                    case 'enable': {
                            OrderPayment::updateOne($ids, ['enabled' => 1]);
                            break;
                        }
                    case 'delete': {
                            foreach ($ids as $id) {
                                if (Order::getOrdersCount(['payment_method_id' => $id]) == 0) {
                                    OrderPayment::deleteOne($id);
                                } else {
                                    Design::assign('message_error', 'order');
                                }
                            }
                            break;
                        }
                }
            }

            foreach (Helper::getPositions() as $id => $position) {
                OrderPayment::updateOne($id, ['position' => $position]);
            }
        }

        // Отображение
        $payment_methods = OrderPayment::getPaymentMethods();
        Design::assign('payment_methods', $payment_methods);

        return $this->fetchResponse('order/payment_list.tpl');
    }
}
