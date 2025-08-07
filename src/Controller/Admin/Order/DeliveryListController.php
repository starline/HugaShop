<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.3
 *
 */

namespace App\Controller\Admin\Order;

use HugaShop\Services\Design;
use HugaShop\Services\Helper;
use HugaShop\Services\Secure;
use HugaShop\Services\Request;
use HugaShop\Models\Order\Order;
use App\Controller\BaseAdminController;
use HugaShop\Models\Order\OrderDelivery;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DeliveryListController extends BaseAdminController
{
    #[Route('/admin/order/deliveries', name: 'DeliveryListAdmin')]
    public function index(): Response
    {

        $this->checkAdminAccess('order_delivery');

        // Обработка действий
        if (Secure::checkCSRF()) {

            // Действия с выбранными
            $ids = Request::post('check');
            if (is_array($ids)) {
                switch (Request::post('action')) {
                    case 'disable': {
                            OrderDelivery::updateList($ids, ['enabled' => 0]);
                            break;
                        }
                    case 'enable': {
                            OrderDelivery::updateList($ids, ['enabled' => 1]);
                            break;
                        }
                    case 'delete': {
                            foreach ($ids as $id) {
                                if (empty(Order::getOrdersCount(['delivery_id' => $id]))) {
                                    OrderDelivery::deleteOne($id);
                                } else {
                                    Design::assign('message_error', 'order');
                                }
                            }
                            break;
                        }
                }
            }

            foreach (Helper::getPositions() as $id => $position) {
                OrderDelivery::updateOne($id, ['position' => $position]);
            }
        }

        // Отображение
        Design::assign('deliveries',  OrderDelivery::getDeliveryMethods());

        return $this->fetchResponse('order/delivery_list.tpl');
    }
}
