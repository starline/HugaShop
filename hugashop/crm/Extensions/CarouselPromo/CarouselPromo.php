<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.6
 *
 */

namespace HugaShop\Extensions\CarouselPromo;

use HugaShop\Extensions\BaseExtension;

final class CarouselPromo extends BaseExtension
{

    /**
     * Get block template
     */
    public static function getTemplate(array $params = [])
    {
        return self::fetchTemplate('carousel.tpl');
    }
}
