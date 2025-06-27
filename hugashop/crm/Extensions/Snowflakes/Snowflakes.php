<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.9
 * 
 * @link https://github.com/Alaev-Co/snowflakes
 *
 */

namespace HugaShop\Extensions\Snowflakes;

use HugaShop\Extensions\BaseExtension;

final class Snowflakes extends BaseExtension
{
    /**
     * Get block template
     */
    public function getFrontBodyTemplate()
    {
        if (!empty($this->settings->enabled) and !empty($this->settings->countSnowflake)) {
            return $this->fetchTemplate('snow.tpl');
        }
        return null;
    }
}
