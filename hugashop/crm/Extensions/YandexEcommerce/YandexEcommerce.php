<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.4
 *
 */

namespace HugaShop\Extensions\YandexEcommerce;

use HugaShop\Extensions\BaseExtension;

final class YandexEcommerce extends BaseExtension
{

    public function getTemplate(array $params = [])
    {
        return $this->fetchTemplate('templates/template.tpl');
    }
}
