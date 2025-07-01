<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.9
 *
 */

namespace App\Controller\Front\User;

use HugaShop\Models\User\User;
use HugaShop\Services\Config;
use HugaShop\Services\Design;
use HugaShop\Services\Helper;
use HugaShop\Services\Request;
use HugaShop\Models\User\UserNotifier;
use App\Controller\BaseFrontController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class UserLoginController extends BaseFrontController
{

    #[Route('/user/login', name: 'UserLogin', priority: 10)]
    public function login(): Response
    {

        if (User::isLoggedIn()) {
            return $this->redirectToRoute('UserOrderList');
        }

        if (Request::checkCSRF()) {

            $email     = Request::post('email', 'string');
            $password  = Request::post('password', 'string');

            Design::assign('email', $email);

            if (empty($email)) {
                Design::append('form_invalid', 'email');
            }
            if (empty($password)) {
                Design::append('form_invalid', 'password');
            }

            if (!empty($email) and !empty($password)) {
                if (User::checkPassword($email, $password)) {

                    $user = User::getUser(['email' => $email]);
                    if ($user->enabled) {

                        Request::setSession('user_id', $user->id);
                        User::updateUser($user->id, ['last_ip' => $_SERVER['REMOTE_ADDR']]);

                        if (!empty(Request::post('remember'))) {
                            User::setRememberMeCookie($user->id);
                        }

                        // Перенаправляем пользователя на прошлую страницу, если она известна
                        if (!empty(Request::getSession('last_visited_page'))) {
                            return $this->redirect(Request::getSession('last_visited_page'));
                        } else {
                            return $this->redirect(Config::get('root_url'));
                        }
                    } else {
                        Design::assign('error', 'user_disabled');
                    }
                } else {
                    Design::assign('error', 'login_incorrect');
                }
            }
        }

        Design::assign('canonical', $this->generateUrl('UserLogin')); # Канонический URL
        Design::assign('noindex', true); # Закрываем от индексации

        return $this->fetchResponse('user_login.tpl');
    }


    #[Route('/user/password-remind', name: 'UserPasswordRemind', priority: 10)]
    public function passwordRemind(): Response
    {

        if (User::isLoggedIn()) {
            return $this->redirectToRoute('User');
        }

        // Если запостили email
        if (Request::checkCSRF()) {

            if (!empty($email = Request::post('email'))) {
                Design::assign('email', $email);

                // Выбираем пользователя из базы
                $user = User::getUser(['email' => $email]);
                if (!empty($user)) {

                    // Генерируем секретный код и сохраняем в сессии
                    $code = Helper::makeToken();
                    Request::setSession('password_remind_code', $code);
                    Request::setSession('password_remind_user_id', $user->id);

                    // Отправляем письмо пользователю для восстановления пароля
                    if (UserNotifier::sendNotifier('Email', 'userPasswordRemind', [
                        'user_id' => $user->id,
                        'code' => $code,
                        'to_email' => $user->email
                    ])) {
                        Design::assign('email_sent', true);
                    }
                } else {
                    Design::assign('error', 'user_not_found');
                }
            } else {
                Design::append('form_invalid', 'email');
            }
        }

        $session_lifetime = ini_get("session.gc_maxlifetime"); # seconds
        $session_lifetime = $session_lifetime / 60; # minutes

        Design::assign('noindex', true); # Закрываем от индексации
        Design::assign('canonical', $this->generateUrl('UserPasswordRemind'));
        Design::assign('session_lifetime', $session_lifetime);

        return $this->fetchResponse('user_password_remind.tpl');
    }


    #[Route('/user/password-remind/{code}', name: 'UserPasswordRemindCode', priority: 10)]
    public function passwordRemindCode(string $code): Response
    {

        if (User::isLoggedIn()) {
            return $this->redirectToRoute('User');
        }

        // Если к нам перешли по ссылке для восстановления пароля
        if (!empty($code)) {
            if (User::checkRemindCode($code) === true) {

                // И переходим в кабинет для изменения пароля
                return $this->redirectToRoute('User');
            } else {
                Design::assign('error', 'invalid_code');
            }
        }

        Design::assign('noindex', true); # Закрываем от индексации
        Design::assign('canonical', $this->generateUrl('UserPasswordRemindCode'));

        return $this->fetchResponse('user_password_remind.tpl');
    }


    #[Route('/user/logout', name: 'UserLogout', priority: 10)]
    public function logout()
    {
        Request::deleteSession('user_id');
        Request::deleteCookie(User::$cookie_uid);
        return $this->redirectToRoute('Main');
    }
}
