<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.5
 *
 */

namespace HugaShop\Extensions\SmsSender;

use HugaShop\Extensions\BaseExtension;


final class SmsSender extends BaseExtension
{
    /**
     * Webhook module
     */
    public function webhook(array $params = [])
    {
        return false;
    }
}
