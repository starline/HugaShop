<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.5
 *
 */

namespace HugaShop\Extensions\BackToTop;

use HugaShop\Extensions\BaseExtension;

final class BackToTop extends BaseExtension
{

    /**
     * Get Head template
     */
    public function getFrontBodyTemplate()
    {
        if (!empty($this->settings->enabled)) {
            return $this->fetchTemplate('button.tpl');
        }
        return;
    }
}
