<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.1
 */

namespace HugaShop\Extensions\GoogleAuth\Controller;

use HugaShop\Services\Config;
use HugaShop\Services\Helper;
use HugaShop\Models\User\User;
use HugaShop\Services\Request;
use App\Controller\BaseFrontController;
use HugaShop\Extensions\BaseExtensionTrait;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class GoogleAuthController extends BaseFrontController
{
    use BaseExtensionTrait;

    #[Route('/google-auth', name: 'ExtGoogleAuth', priority: 1)]
    public function auth(): Response
    {
        if (User::isLoggedIn()) {
            return $this->redirectToRoute('User');
        }

        $settings       = $this->getExtension()->settings;
        $clientId       = $settings->client_id ?? null;
        $clientSecret   = $settings->client_secret ?? null;

        if (empty($clientId) || empty($clientSecret)) {
            return $this->redirectToRoute('UserRegister');
        }

        $redirectUri = Config::get('root_url') . $this->generateUrlWithLocale('ExtGoogleAuth');
        $code = Request::get('code', 'string');

        if (empty($code)) {
            $params = http_build_query([
                'response_type'     => 'code',
                'client_id'         => $clientId,
                'redirect_uri'      => $redirectUri,
                'scope'             => 'email profile',
                'prompt'            => 'select_account'
            ]);

            return $this->redirect('https://accounts.google.com/o/oauth2/v2/auth?' . $params);
        }

        $params = [
            'code'          => $code,
            'client_id'     => $clientId,
            'client_secret' => $clientSecret,
            'redirect_uri'  => $redirectUri,
            'grant_type'    => 'authorization_code'
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
                'name'      => $info['name'] ?? $info['email'],
                'email'     => $info['email'],
                'password'  => Helper::makeToken($info['email']),
                'enabled'   => 1
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
