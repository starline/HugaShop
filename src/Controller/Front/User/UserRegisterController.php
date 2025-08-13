<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 3.6
 *
 */

namespace App\Controller\Front\User;

use HugaShop\Services\Config;
use HugaShop\Services\Design;
use HugaShop\Services\Helper;
use HugaShop\Services\Secure;
use HugaShop\Models\User\User;
use HugaShop\Services\Request;
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

        if (Secure::checkCSRF()) {

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

                        $user = User::addUser([
                            'name' =>       $name,
                            'email' =>      $email,
                            'password' =>   $password,
                            'enabled' =>    1 # Активен ли пользователь сразу после регистрации (0 или 1)
                        ]);

                        Request::setSession('user_id', $user->id);
                        User::setRememberMeCookie($user->id); # Запоминаем пользователя

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

        return $this->fetchResponse('user/user_register.tpl');
    }

    #[Route('/user/register/google', name: 'UserRegisterGoogle', priority: 10)]
    public function registerGoogle(): Response
    {
        if (User::isLoggedIn()) {
            return $this->redirectToRoute('User');
        }

        $google = Config::get('google_oauth');
        $redirectUri = Config::get('root_url') . '/user/register/google';
        $code = Request::get('code', 'string');

        if (empty($code)) {
            $params = http_build_query([
                'response_type' => 'code',
                'client_id' => $google->client_id,
                'redirect_uri' => $redirectUri,
                'scope' => 'email profile',
                'prompt' => 'select_account'
            ]);
            return $this->redirect('https://accounts.google.com/o/oauth2/v2/auth?' . $params);
        }

        $params = [
            'code' => $code,
            'client_id' => $google->client_id,
            'client_secret' => $google->client_secret,
            'redirect_uri' => $redirectUri,
            'grant_type' => 'authorization_code'
        ];

        $ch = curl_init('https://oauth2.googleapis.com/token');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        $tokenResponse = curl_exec($ch);
        curl_close($ch);
        $tokenInfo = json_decode($tokenResponse, true);

        if (empty($tokenInfo['access_token'])) {
            return $this->redirectToRoute('UserRegister');
        }

        $ch = curl_init('https://www.googleapis.com/oauth2/v2/userinfo');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $tokenInfo['access_token']]);
        $userInfoResponse = curl_exec($ch);
        curl_close($ch);
        $info = json_decode($userInfoResponse, true);

        if (empty($info['email'])) {
            return $this->redirectToRoute('UserRegister');
        }

        if (User::checkEmailExists($info['email'])) {
            $user = User::getUser(['email' => $info['email']]);
        } else {
            $user = User::addUser([
                'name' => $info['name'] ?? $info['email'],
                'email' => $info['email'],
                'password' => Helper::makeToken($info['email']),
                'enabled' => 1
            ]);
        }

        Request::setSession('user_id', $user->id);
        User::setRememberMeCookie($user->id);

        if (!empty(Request::getSession('last_visited_page'))) {
            return $this->redirect(Request::getSession('last_visited_page'));
        }

        return $this->redirectToRoute('Main');
    }
}
