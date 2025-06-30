<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.3
 * 
 * status: 0 - Новый, 1 - Принят, 4 - Отгружен,  2 - Выполнен, 3 - Отмена
 *
 */

namespace App\Controller\Admin\Order;

use HugaShop\Services\Design;
use HugaShop\Models\Request;
use App\Services\PaginationService;
use HugaShop\Models\Order\Order;
use HugaShop\Models\User\UserPermission;
use HugaShop\Models\Order\OrderLabel;
use HugaShop\Models\Order\OrderPurchase;
use App\Controller\BaseAdminController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class OrderListController extends BaseAdminController
{

    #[Route('/admin/orders', name: 'OrderListAdmin')]
    public function index(): Response
    {

        $this->checkAdminAccess('order');

        // Обработка действий
        if (Request::checkCSRF()) {

            // Действия с выбранными
            $ids = Request::post('check');
            if (is_array($ids)) {
                switch (Request::post('action')) {
                    case 'delete': {
                            foreach ($ids as $id) {
                                $order = Order::getOrder(intval($id));

                                // Если заказ Новый(0) Принят(1) Выполнен(2) Отгружен(4)
                                if ($order->status < 3 || $order->status == 4) {
                                    Order::updateOrder($id, array('status' => 3));
                                    Order::open($id);
                                }
                                // Если заказ Отменен(3) - удаляем из базы
                                elseif (UserPermission::checkAccess('order_delete')) {
                                    Order::deleteOrder($id);
                                }
                            }
                            break;
                        }
                    case 'set_status_0': {
                            foreach ($ids as $id) {
                                if (Order::open(intval($id))) {
                                    Order::updateOrder($id, array('status' => 0));
                                }
                            }
                            break;
                        }
                    case 'set_status_1': {
                            foreach ($ids as $id) {
                                if (!Order::close(intval($id))) {
                                    Design::assign('message_error', 'error_closing');
                                } else {
                                    Order::updateOrder($id, array('status' => 1));
                                }
                            }
                            break;
                        }
                    case 'set_status_2': {
                            foreach ($ids as $id) {
                                if (!Order::close(intval($id))) {
                                    Design::assign('message_error', 'error_closing');
                                } else {
                                    Order::updateOrder($id, array('status' => 2));
                                }
                            }
                            break;
                        }
                    case 'set_status_4': {
                            foreach ($ids as $id) {
                                if (!Order::close(intval($id))) {
                                    Design::assign('message_error', 'error_closing');
                                } else {
                                    Order::updateOrder($id, array('status' => 4));
                                }
                            }
                            break;
                        }
                    case (preg_match('/^set_label_([0-9]+)/', Request::post('action'), $a) ? true : false): {
                            $l_id = intval($a[1]);
                            if ($l_id > 0) {
                                foreach ($ids as $id) {
                                    OrderLabel::addOrderLabels($id, $l_id);
                                }
                            }
                            break;
                        }
                    case (preg_match('/^unset_label_([0-9]+)/', Request::post('action'), $a) ? true : false): {
                            $l_id = intval($a[1]);
                            if ($l_id > 0) {
                                foreach ($ids as $id) {
                                    OrderLabel::deleteOrderLabels($id, $l_id);
                                }
                            }
                            break;
                        }
                }
            }
        }

        $filter = PaginationService::initFilter();

        // Поиск
        $keyword = Request::get('keyword', 'string');
        if (!empty($keyword)) {
            $filter['keyword'] = $keyword;
            Design::assign('keyword', $keyword);
        }

        // Фильтр по метке
        $this->applyLabelFilter($filter);

        // Оплачены/Не оплачены
        $paid = Request::get('paid', "integer");
        $filter['paid'] = $paid;
        Design::assign('paid', $paid);


        if (empty($keyword)) {

            // если status не задан, ставим 0
            if (!$status = Request::getInt('status')) {
                $status = 0;
            }

            $filter['status'] = $status;
            Design::assign('status', $status);
        }


        // Ограничиваем просмотр кол-во страниц
        // для выполненых(2) отмененых(3) и поиска(keyword)
        if (((isset($status) && ($status == 3 || $status == 2)) || !empty($keyword)) && !UserPermission::checkAccess('order_view_all')) {
            $filter['page'] = 1;
            Design::assign('pagination_hide', true);
        }

        // Выбираем все заказы
        $orders = Order::getOrders($filter, join: [
            'delivery_method',
            'payment_method',
            'purchases',
            'purchases.product',
            'purchases.product.image',
            'labels'
        ]);
        $orders_count   = Order::getOrdersCount($filter);
        $orders_price   = Order::getOrdersPrice($filter); # Выбираем общую сумму заказов
        $labels         = OrderLabel::getLabels(); # Метки заказов

        
        Design::assign('pagination', PaginationService::getPagination($orders_count, $filter));
        Design::assign('orders_count', $orders_count);
        Design::assign('orders_price', $orders_price);
        Design::assign('orders', $orders);
        Design::assign('labels', $labels);

        return $this->fetchResponse('order/order_list.tpl');
    }


    /**
     * Filter label
     */
    private function applyLabelFilter(&$filter)
    {
        if ($label_id = Request::getInt('label')) {
            if ($label = OrderLabel::getOne($label_id)) {
                $filter['label'] = $label->id;
                Design::assign('label', $label);
            }
        }
    }
}
