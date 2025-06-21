<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.3
 *
 */

namespace HugaShop\Extensions\CarouselPromo;

use HugaShop\Models\Request;
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

        return $this->getTemplatePath('index.tpl');
    }

    /**
     * Get block template
     */
    public function getTemplate(array $params = [])
    {
        return $this->fetchTemplate('carousel.tpl');
    }
}
