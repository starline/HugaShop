<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.5
 * @link https://enhancedecommerce.appspot.com/
 *
 */

namespace HugaShop\Extensions\GoogleDataLayer;

use HugaShop\Extensions\BaseExtension;

final class GoogleDataLayer extends BaseExtension
{
    /**
     * Get block template
     */
    public function getFrontBodyTemplate()
    {
        if (!empty($this->ext_settings->enabled)) {
            return $this->fetchTemplate('datalayer.tpl');
        }
        return;
    }
}
