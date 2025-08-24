<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.8
 *
 */

namespace HugaShop\Addons\CarouselPromo;

use HugaShop\Addons\BaseAddon;
use HugaShop\Services\Design;
use HugaShop\Addons\CarouselPromo\Models\CarouselPromoBanner;

final class CarouselPromo extends BaseAddon
{

    /**
     * Get block template
     */
    public static function getTemplate(array $params = [])
    {
        Design::assign('banners', CarouselPromoBanner::getList(filter: ['enabled' => 1], order: 'position', join: ['image']));
        Design::assign('addon', self::getAddon());

        return self::fetchTemplate('carousel.tpl');
    }
}
