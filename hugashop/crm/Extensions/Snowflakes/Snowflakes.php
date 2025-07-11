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
        if (!empty(self::getSettings()->enabled) and !empty(self::getSettings()->countSnowflake)) {
            return $this->fetchTemplate('templates/snow.tpl');
        }
        return null;
    }
}
