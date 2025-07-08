<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 3.0
 *
 */

namespace App\Controller\Front\User;

use HugaShop\Models\User\User;
use HugaShop\Models\Order\Order;
use HugaShop\Services\Design;
use HugaShop\Services\Request;
use App\Controller\BaseFrontController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class UserController extends BaseFrontController
{

    #[Route('/user', name: 'User', priority: 10)]
    public function user(): Response
    {

        if (empty(User::isLoggedIn())) {
            return $this->redirectToRoute('UserLogin');
        }

        $this->handleUserUpdate();

        $user = User::authUser();
        $orders = Order::getOrders(['user_id' => $user->id]);

        Design::assign('orders', $orders);
        Design::assign('user', $user);
        Design::assign('noindex', true); # Запрет индексации страницы

        return $this->fetchResponse('user/user.tpl');
    }

    
    /**
     * User update
     */
    public function handleUserUpdate()
    {
        if (!Request::checkCSRF()) {
            return false;
        }

        $user_id  = User::authUser('id');
        $name     = Request::post('name', 'string');
        $email    = Request::post('email', 'string');
        $password = Request::post('password', 'string');

        if (empty($name)) {
            Design::append('form_invalid', 'name');
        } elseif (empty($email)) {
            Design::append('form_invalid', 'email');
        } else {

            $email_exists = User::checkEmailExists($email, $user_id);
            if ($email_exists) {
                Design::assign('error', 'email_exists');
            } else {
                User::updateUser($user_id, ['name' => $name, 'email' => $email]);
                Design::assign('success', true);
            }
        }

        // TODO: Проверять старый пароль и пароль на сложность

        if (!empty($password)) {
            User::updateUser($user_id, ['password' => $password]);
        }
    }
}
