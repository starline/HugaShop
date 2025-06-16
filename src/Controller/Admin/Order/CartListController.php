<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.0
 *
 */

namespace App\Controller\Admin\Order;

use HugaShop\Api\Design;
use HugaShop\Api\Helper;
use HugaShop\Api\Request;
use HugaShop\Api\Settings;
use HugaShop\Api\Cart\Cart;
use HugaShop\Api\User\User;
use HugaShop\Api\Order\Order;
use HugaShop\Api\Cart\CartPurchase;
use App\Controller\BaseAdminController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CartListController extends BaseAdminController
{
    #[Route('/admin/order/carts', name: 'CartListAdmin')]
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
                                Cart::deleteCart($id);
                            }
                            break;
                        }
                }
            }
        }

        $filter = [];
        $filter['page'] = max(1, Request::get('page', 'int'));
        $filter['limit'] = Request::get('page', 'string') == 'all' ? 'all' : Settings::getParam('products_num_admin');

        $carts = Cart::getList($filter, join: ['user', 'order']);
        $carts_count = Cart::getCount($filter);

        foreach ($carts as $cart) {
            if (!empty($cart->user_agent)) {
                $cart->user_agent = Helper::getUserAgentInfo($cart->user_agent);
            }
            if (!empty($cart->referral) and !empty($gets = @unserialize($cart->referral))) {
                $cart->referral = Cart::getReferral($gets);
            }
        }

        Design::assign('pages_count', ceil($carts_count / Settings::getParam('products_num_admin')));
        Design::assign('current_page', $filter['limit'] == 'all' ? 'all' : $filter['page']);
        Design::assign('carts', $carts);
        Design::assign('carts_count', $carts_count);

        // Отображение
        return $this->fetchResponse('order/cart_list.tpl');
    }
}
