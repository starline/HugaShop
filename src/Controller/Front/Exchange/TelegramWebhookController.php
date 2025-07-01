<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.6
 *
 */

namespace App\Controller\Front\Exchange;

use HugaShop\Services\Config;
use TelegramBot\NotifyBot;
use HugaShop\Models\User\UserNotifier;
use HugaShop\Models\User\UserPermission;;
use App\Controller\BaseFrontController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class TelegramWebhookController extends BaseFrontController
{
    #[Route('/exchange/telegram', name: 'TelegramWebhook')]
    public function index()
    {
        try {

            // TODO: добавить notifier id

            $notifier_settings = UserNotifier::getNotifierSettings('Telegram');

            $Bot = new NotifyBot();
            $Bot->debugOn();
            $Bot->setUserConfig([
                'api_key' =>  $notifier_settings->api_key,
                'bot_username' =>  $notifier_settings->bot_username,
                'admins' => [$notifier_settings->admins],
                'webhook' => [
                    'url' => Config::get('root_url') . '/exchange/telegram',
                    'secret_token' =>  $notifier_settings->secret_token,
                    'max_connections' => 10
                ],
                'mysql'        => [
                    'host'     => Config::get('database')->server,
                    'user'     => Config::get('database')->user,
                    'password' => Config::get('database')->password,
                    'database' => $notifier_settings->database
                ],
                'logging' => [
                    'error'  => Config::get('log_dir') . 'TelegramError.log',
                    'debug'  => Config::get('log_dir') . 'TelegramDebug.log',
                    'update' => Config::get('log_dir') . 'TelegramUpdate.log',
                ]
            ]);


            // If admin
            if (UserPermission::checkAccess('settings') === true) {
                $Bot->run(true);
            } else {
                $Bot->run(false);
            }

            return new Response();
        } catch (\Throwable $e) {

            // Prevent Telegram from retrying
            return new Response('Caught exception: ' . $e->getMessage() . PHP_EOL);
        }
    }
}
