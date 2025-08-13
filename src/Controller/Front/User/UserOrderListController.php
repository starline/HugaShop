<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.3
 *
 */

namespace App\Controller\Front\User;

use HugaShop\Models\User\User;
use HugaShop\Services\Design;
use HugaShop\Models\Order\Order;
use App\Services\PaginationService;
use App\Controller\BaseFrontController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class UserOrderListController extends BaseFrontController
{

    #[Route('/user/orders', name: 'UserOrderList', priority: 10)]
    public function orders(): Response
    {

        if (!User::isLoggedIn()) {
            return $this->redirectToRoute('UserLogin');
        }

        $user = User::authUser();

        $filter = PaginationService::initFilter(per_page: 10);
        $filter['user_id'] = $user->id;

        $orders = Order::getOrders($filter, join: [
            'purchases',
            'purchases.product',
            'purchases.product.image'
        ]);
        $orders_count = Order::getOrdersCount($filter);

        Design::assign('orders', $orders);
        Design::assign('pagination', PaginationService::getPagination($orders_count, $filter));
        Design::assign('noindex', true); # Запрет индексации страницы

        return $this->fetchResponse('user/user_order_list.tpl');
    }
}
