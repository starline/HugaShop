<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.5
 *
 */

namespace HugaShop\Extensions\YandexEcommerce;

use HugaShop\Extensions\BaseExtension;

final class YandexEcommerce extends BaseExtension
{

    public static function getTemplate(array $params = [])
    {
        return self::fetchTemplate('template.tpl');
    }
}
