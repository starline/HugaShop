<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.7
 *
 */

namespace App\Controller\Admin\User;

use HugaShop\Api\User\User;
use HugaShop\Api\Design;
use HugaShop\Api\Request;
use HugaShop\Api\Settings;
use HugaShop\Api\User\UserGroup;
use HugaShop\Api\Order\Order;
use HugaShop\Api\User\UserNotifier;
use HugaShop\Api\User\UserPermission;
use HugaShop\Api\Order\OrderLabel;
use HugaShop\Api\Order\OrderPurchase;
use App\Controller\BaseAdminController;
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
        if (!empty($current_user = Request::getDataAcces(User::$table_fields))) {

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
                    UserNotifier::updateUserNotifierTypes($current_user->id, null);
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

        $filter = [];
        $filter['page'] = max(1, Request::get('page', 'int'));
        $filter['limit'] = Request::get('page', 'string') == 'all' ? 'all' : Settings::getParam('products_num_admin');
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

        $groups = UserGroup::orderBy('position')->get(); # Выбираем все группы пользователей

        Design::assign('pages_count', ceil($orders_count / Settings::getParam('products_num_admin')));
        Design::assign('current_page', $filter['limit'] == 'all' ? 'all' : $filter['page']);

        Design::assign([
            'current_user' => $current_user,
            'groups' => $groups,
            'orders' => $orders,
            'orders_count' => $orders_count,
            'orders_price' => $orders_price
        ]);

        return $this->fetchResponse('user/user.tpl');
    }
}
