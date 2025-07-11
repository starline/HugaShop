<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.6
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
        if (!empty(self::getSettings()->enabled)) {
            return self::fetchTemplate('templates/button.tpl');
        }
        return;
    }
}
