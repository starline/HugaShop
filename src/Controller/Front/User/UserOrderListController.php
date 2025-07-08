<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.2
 *
 */

namespace App\Controller\Front\User;

use HugaShop\Models\User\User;
use HugaShop\Services\Design;
use HugaShop\Models\Order\Order;
use HugaShop\Models\Order\OrderPurchase;
use App\Controller\BaseFrontController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class UserOrderListController extends BaseFrontController
{

    #[Route('/user/orders', name: 'UserOrderList')]
    public function orders(): Response
    {

        if (empty(User::isLoggedIn())) {
            return $this->redirectToRoute('UserLogin');
        }

        $user = User::authUser();
        $orders = Order::getOrders(['user_id' => $user->id], join: [
            'purchases',
            'purchases.product',
            'purchases.product.image'
        ]);

        Design::assign('orders', $orders);
        Design::assign('noindex', true); # Запрет индексации страницы

        return $this->fetchResponse('user/user_order_list.tpl');
    }
}
