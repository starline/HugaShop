<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.1
 *
 */

namespace App\Controller\Admin\User;

use HugaShop\Services\Design;
use HugaShop\Models\User\User;
use HugaShop\Services\Request;
use HugaShop\Models\Order\Order;
use App\Services\PaginationService;
use HugaShop\Models\User\UserGroup;
use App\Controller\BaseAdminController;
use HugaShop\Models\User\UserPermission;
use HugaShop\Models\User\UserNotifierType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class UserController extends BaseAdminController
{
    #[Route('/admin/user/{id}', requirements: ['id' => '\d+'], name: 'UserAdmin')]
    public function index(int $id): Response
    {

        $this->checkAdminAccess('user');


        #### Update
        ###########
        if (!empty($current_user = Request::getInputCheckEditAccess(User::class, $id))) {

            // Не допустить одинаковые email пользователей
            // Разрешить пустые email
            if (!empty($current_user->email) and ($u_check = User::getUser(['email' => $current_user->email])) and $u_check->id !== $current_user->id) {
                Design::setFlashMessage('error', 'email_exists');
            }

            // Не допустить одинаковые телефон пользователей
            elseif (!empty($current_user->phone) and ($u_check = User::getUser(['phone' => $current_user->phone])) and $u_check->id !== $current_user->id) {
                Design::setFlashMessage('error', 'phone_exists');
            }

            // Update User data
            else {
                Design::setFlashMessage('update', User::updateUser(intval($current_user->id), $current_user));

                // If NOT already a manager - clear permission data
                if (isset($current_user->manager) and $current_user->manager == 0) {
                    UserPermission::updatePermissions($current_user->id, []);
                    UserNotifierType::updateTypes($current_user->id, null);
                }

                // Делаем редирект на страницу с ID
                return $this->redirectToRoute('UserAdmin', ['id' => $current_user->id]);
            }
        }


        #### View
        #########
        if (empty($current_user = User::getUser($id))) {
            return $this->redirectToRoute('UserListAdmin');
        }

        $filter = PaginationService::initFilter();
        $filter['user_id'] = $current_user->id;


        $orders_count = Order::getOrdersCount($filter); # Кол-во заказов
        $orders = Order::getOrders($filter, join: [
            'delivery_method',
            'payment_method',
            'labels',
            'purchases',
            'purchases.product',
            'purchases.product.image'
        ]);


        // Выбираем общую сумму заказов
        $filter["paid"] = 1; # оплаченые
        $orders_price = Order::getOrdersPrice($filter);

        Design::assign('pagination', PaginationService::getPagination($orders_count, $filter));
        Design::assign('current_user',      $current_user);
        Design::assign('groups',            UserGroup::getList(order: 'position'));   # Выбираем все группы пользователей
        Design::assign('orders',            $orders);
        Design::assign('orders_count',      $orders_count);
        Design::assign('orders_price',      $orders_price);

        return $this->fetchResponse('user/user.tpl');
    }
}
