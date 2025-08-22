<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.2
 * 
 * @link https://github.com/Alaev-Co/snowflakes
 *
 */

namespace HugaShop\Addons\Snowflakes;

use HugaShop\Addons\BaseAddon;

final class Snowflakes extends BaseAddon
{
    /**
     * Get block template
     */
    public static function getFrontBodyTemplate()
    {
        if (!empty(self::getSettings()->enabled) and !empty(self::getSettings()->countSnowflake)) {
            return self::fetchTemplate('snow.tpl');
        }
        return;
    }
}
