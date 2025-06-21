<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 3.3
 *
 */

namespace App\Controller\Front\User;

use HugaShop\Models\User\User;
use HugaShop\Models\Design;
use HugaShop\Models\Helper;
use HugaShop\Models\Request;
use App\Controller\BaseFrontController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class UserRegisterController extends BaseFrontController
{

    #[Route('/user/register', name: 'UserRegister', priority: 10)]
    public function register(): Response
    {

        if (User::isLoggedIn()) {
            return $this->redirectToRoute('User');
        }

        if (Request::checkCSRF()) {

            $name            = Request::post('name', 'string');
            $email           = Request::post('email', 'string');
            $password        = Request::post('password', 'string');

            Design::assign('name', $name);
            Design::assign('email', $email);

            if (Helper::checkCaptcha()) {

                if (empty($name)) {
                    Design::append('form_invalid', 'name');
                }
                if (empty($email)) {
                    Design::append('form_invalid', 'email');
                }
                if (empty($password)) {
                    Design::append('form_invalid', 'password');
                }

                if (!empty($password) and !empty($email) and !empty($password)) {

                    if (User::checkEmailExists($email)) {
                        Design::assign('error', 'user_exists');
                    } else {

                        $user_id = User::addUser([
                            'name' =>       $name,
                            'email' =>      $email,
                            'password' =>   $password,
                            'enabled' =>    1 # Активен ли пользователь сразу после регистрации (0 или 1)
                        ]);

                        Request::setSession('user_id', $user_id);
                        User::setRememberMeCookie($user_id); # Запоминаем пользователя

                        // TODO: отправить email о регистрации и подтверждении email

                        if (!empty(Request::getSession('last_visited_page'))) {
                            return $this->redirect(Request::getSession('last_visited_page'));
                        } else {
                            return $this->redirectToRoute('Main');
                        }
                    }
                }
            } else {
                Design::assign('error', 'captcha');
            }
        }

        Design::assign('noindex', true); # Закрываем от индексации
        Design::assign('canonical', $this->generateUrl('UserRegister'));

        return $this->fetchResponse('user_register.tpl');
    }
}
