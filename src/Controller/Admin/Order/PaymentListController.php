<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.2
 *
 */

namespace App\Controller\Admin\Order;

use HugaShop\Services\Design;
use HugaShop\Services\Helper;
use HugaShop\Services\Secure;
use HugaShop\Services\Request;
use HugaShop\Models\Order\Order;
use App\Controller\BaseAdminController;
use HugaShop\Models\Order\OrderPayment;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PaymentListController extends BaseAdminController
{
    #[Route('/admin/order/payments', name: 'OrderPaymentListAdmin')]
    public function index(): Response
    {

        $this->checkAdminAccess('order_payment');

        // Обработка действий
        if (Secure::checkCSRF()) {

            // Действия с выбранными
            $ids = Request::post('check');
            if (is_array($ids)) {
                switch (Request::post('action')) {
                    case 'disable': {
                            OrderPayment::updateList($ids, ['enabled' => 0]);
                            break;
                        }
                    case 'enable': {
                            OrderPayment::updateList($ids, ['enabled' => 1]);
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
