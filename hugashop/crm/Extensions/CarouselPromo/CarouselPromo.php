<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.6
 *
 */

namespace HugaShop\Extensions\CarouselPromo;

use HugaShop\Services\Request;
use HugaShop\Extensions\BaseExtension;

final class CarouselPromo extends BaseExtension
{
    /**
     * For admin panel use default settings template
     */
    public function index()
    {

        // Обработка действий
        if (Request::checkCSRF()) {
            //...
        }

        return $this->getTemplatePath('templates/index.tpl');
    }

    /**
     * Get block template
     */
    public static function getTemplate(array $params = [])
    {
        return self::fetchTemplate('templates/carousel.tpl');
    }
}
