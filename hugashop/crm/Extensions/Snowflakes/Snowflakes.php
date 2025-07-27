<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.1
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
    public static function getFrontBodyTemplate()
    {
        if (!empty(self::getSettings()->enabled) and !empty(self::getSettings()->countSnowflake)) {
            return self::fetchTemplate('snow.tpl');
        }
        return;
    }
}
