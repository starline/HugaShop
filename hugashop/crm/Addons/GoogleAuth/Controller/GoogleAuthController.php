<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.4
 */

namespace HugaShop\Addons\GoogleAuth\Controller;

use HugaShop\Services\Config;
use HugaShop\Services\Helper;
use HugaShop\Models\User\User;
use HugaShop\Services\Request;
use App\Controller\BaseFrontController;
use HugaShop\Addons\BaseAddonTrait;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Google\Client;
use Google\Service\Oauth2;

final class GoogleAuthController extends BaseFrontController
{
    use BaseAddonTrait;

    #[Route('/google-auth', name: 'AddonGoogleAuth', priority: 1)]
    public function auth(): Response
    {

        $state = Request::get('state', 'string');

        if (User::isLoggedIn()) {
            if ($state === 'popup') {
                return $this->popupResponse();
            }
            return $this->redirectToRoute('User');
        }


        $settings       = $this->getAddon()->settings;
        $clientId       = $settings->client_id ?? null;
        $clientSecret   = $settings->client_secret ?? null;

        if (empty($clientId) || empty($clientSecret)) {
            if ($state === 'popup') {
                return $this->popupResponse();
            }
            return $this->redirectToRoute('UserRegister');
        }

        $redirectUri    = Config::get('root_url') . $this->generateUrlWithLocale('AddonGoogleAuth');
        $code           = Request::get('code', 'string');
        $isPopup        = Request::get('popup', 'bool') || $state === 'popup';

        $client = new Client();
        $client->setClientId($clientId);
        $client->setClientSecret($clientSecret);
        $client->setRedirectUri($redirectUri);
        $client->setScopes(['email', 'profile']);
        $client->setPrompt('select_account');

        if ($isPopup) {
            $client->setState('popup');
        }

        if (empty($code)) {
            return $this->redirect($client->createAuthUrl());
        }

        $tokenInfo = $client->fetchAccessTokenWithAuthCode($code);

        if (empty($tokenInfo['access_token'])) {
            if ($state === 'popup') {
                return $this->popupResponse();
            }
            return $this->redirectToRoute('UserRegister');
        }

        $oauth2 = new Oauth2($client);
        $info   = $oauth2->userinfo->get();

        if (empty($info->getEmail())) {
            if ($state === 'popup') {
                return $this->popupResponse();
            }
            return $this->redirectToRoute('UserRegister');
        }

        $email = $info->getEmail();
        $name  = $info->getName() ?: $email;

        if (User::checkEmailExists($email)) {
            $user = User::getUser(['email' => $email]);
        } else {
            $user = User::addUser([
                'name'      => $name,
                'email'     => $email,
                'password'  => Helper::makeToken($email),
                'enabled'   => 1
            ]);
        }

        Request::setSession('user_id', $user->id);
        User::setRememberMeCookie($user->id);

        if ($state === 'popup') {
            return $this->popupResponse();
        }

        if (!empty(Request::getSession('last_visited_page'))) {
            return $this->redirect(Request::getSession('last_visited_page'));
        }

        return $this->redirectToRoute('Main');
    }


    /**
     * Popup Response
     */
    private function popupResponse()
    {
        return new Response('<script>window.opener.location.reload();window.close();</script>');
    }
}
