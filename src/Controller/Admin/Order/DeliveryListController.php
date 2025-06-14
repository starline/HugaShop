<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.0
 *
 */

namespace App\Controller\Admin\Order;

use HugaShop\Api\Order\Order;
use HugaShop\Api\Design;
use HugaShop\Api\Helper;
use HugaShop\Api\Request;
use HugaShop\Api\Order\OrderDelivery;
use App\Controller\BaseAdminController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DeliveryListController extends BaseAdminController
{
    #[Route('/admin/order/deliveries', name: 'DeliveryListAdmin')]
    public function index(): Response
    {

        $this->checkAdminAccess('order_delivery');

        // Обработка действий
        if (Request::checkCSRF()) {

            // Действия с выбранными
            $ids = Request::post('check');
            if (is_array($ids)) {
                switch (Request::post('action')) {
                    case 'disable': {
                            OrderDelivery::updateOne($ids, ['enabled' => 0]);
                            break;
                        }
                    case 'enable': {
                            OrderDelivery::updateOne($ids, ['enabled' => 1]);
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
