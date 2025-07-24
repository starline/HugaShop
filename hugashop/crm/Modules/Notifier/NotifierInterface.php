<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.0
 */

namespace HugaShop\Modules\Notifier;

interface NotifierInterface
{
    /**
     * Send notification message
     *
     * @param string $message
     * @param array $params
     * @return mixed
     */
    public static function send(string $message, array $params);
}
