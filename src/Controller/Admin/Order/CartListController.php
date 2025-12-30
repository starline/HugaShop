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
use HugaShop\Models\Cart\Cart;
use HugaShop\Services\Request;
use App\Services\PaginationService;
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
        if (Secure::checkCSRF()) {

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

        $filter = PaginationService::initFilter();

        $carts          = Cart::getList($filter, order: ['id', 'desc'], join: ['user', 'order']);
        $carts_count    = Cart::getCount($filter);

        foreach ($carts as $cart) {
            if (!empty($cart->user_agent)) {
                $cart->user_agent = Helper::getUserAgentInfo($cart->user_agent);
            }
            if (!empty($cart->referral) and !empty($get_vars = @unserialize($cart->referral))) {
                $cart->referral = Cart::getReferral($get_vars);
            }
        }

        Design::assign('pagination', PaginationService::getPagination($carts_count, $filter));
        Design::assign('carts', $carts);
        Design::assign('carts_count', $carts_count);

        // Отображение
        return $this->fetchResponse('order/cart_list.tpl');
    }
}
