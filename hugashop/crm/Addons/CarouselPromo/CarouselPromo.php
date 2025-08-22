<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.7
 *
 */

namespace HugaShop\Addons\CarouselPromo;

use HugaShop\Addons\BaseAddon;

final class CarouselPromo extends BaseAddon
{

    /**
     * Get block template
     */
    public static function getTemplate(array $params = [])
    {
        return self::fetchTemplate('carousel.tpl');
    }
}
