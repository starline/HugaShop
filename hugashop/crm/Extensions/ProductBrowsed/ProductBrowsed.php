<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.1
 *
 */

namespace HugaShop\Extensions\ProductBrowsed;

use HugaShop\Extensions\BaseExtension;

final class ProductBrowsed extends BaseExtension
{
    

    /**
     * Get Head template
     */
    public function getTemplate()
    {
        if (!empty($this->settings->enabled)) {
            return $this->fetchTemplate('templates/product_browsed.tpl');
        }
        return;
    }
}
