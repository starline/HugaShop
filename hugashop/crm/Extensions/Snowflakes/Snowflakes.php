<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.0
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

            return self::fetchTemplate('templates/snow.tpl');
        }
        return null;
    }
}
