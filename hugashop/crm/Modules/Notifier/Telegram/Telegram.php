<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.3
 *
 */

namespace HugaShop\Modules\Notifier\Telegram;

use HugaShop\Modules\Notifier\NotifierInterface;
use HugaShop\Services\Config;
use TelegramBot\NotifyBot;

class Telegram implements NotifierInterface
{
    /**
     * Send Message to telegram Chat
     * @param string $message
     * @param array $message_params
     *
     */
    public static function send(String $message, array $params)
    {

        if (!empty($params['user']->te_chat_id) and empty($params['chat_id'])) {
            $params['chat_id'] = $params['user']->te_chat_id;
        }

        if (empty($params['chat_id']) || empty($message) || empty($params['api_key']) || empty($params['bot_username']) || empty($params['database'])) {
            return false;
        }

        // Set default [parse_mode]
        if (empty($params['parse_mode'])) {
            $params['parse_mode'] = 'HTML';
        }

        // TelegramBot initialization
        $TelegrtamBot = new NotifyBot();
        $TelegrtamBot->debugOn();
        $TelegrtamBot->setUserConfig([
            'api_key' =>  $params['api_key'],
            'bot_username' => $params['bot_username'],
            'mysql'        => [
                'host'     => Config::get('database')->server,
                'user'     => Config::get('database')->user,
                'password' => Config::get('database')->password,
                'database' => $params['database']
            ],
            'logging' => [
                'error'  => Config::get('log_dir') . 'TelegramError.log',
                'debug'  => Config::get('log_dir') . 'TelegramDebug.log',
                'update' => Config::get('log_dir') . 'TelegramUpdate.log',
            ]
        ]);
        $TelegrtamBot->initialize();

        return $TelegrtamBot->sendMessage($params, $message);
    }
}
