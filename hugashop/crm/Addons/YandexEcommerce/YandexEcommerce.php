<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.6
 *
 */

namespace HugaShop\Addons\YandexEcommerce;

use HugaShop\Addons\BaseAddon;

final class YandexEcommerce extends BaseAddon
{

    public static function getTemplate(array $params = [])
    {
        return self::fetchTemplate('template.tpl');
    }
}
