<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.0
 *
 */

namespace HugaShop\Modules\Notifier\Turbosms;

use Turbosms\TurboSmsSender;

class Turbosms
{
    /**
     * Send Message via Turbosms
     * @param string $message
     * @param array $params
     *
     */
    public function send(String $message, array $params)
    {

        // Phone number fromm User
        if (!empty($params['user']->phone)) {
            $params['phone'] = $params['user']->phone;
        }

        // Phone number from order
        if (!empty($params['order']->phone)) {
            $params['phone'] = $params['order']->phone;
        }

        if (empty($params['phone']) || empty($message)) {
            return false;
        }

        $SMS = TurboSmsSender::init(
            $params['login'],
            $params['password'],
            $params['name']
        );

        // Отправляем СМС
        return $SMS->sendSms($params['phone'], $message);
    }
}
