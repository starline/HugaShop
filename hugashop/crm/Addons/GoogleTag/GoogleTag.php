<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.7
 *
 */

namespace HugaShop\Addons\GoogleTag;

use HugaShop\Addons\BaseAddon;

final class GoogleTag extends BaseAddon
{

    /**
     * Get Head block template
     */
    public static function getFrontHeadTemplate()
    {
        if (self::isEnabled()) {
            return self::fetchTemplate('head_tag.tpl');
        }
        return;
    }


    /**
     * Get Body block template
     */
    public static function getFrontBodyTemplate()
    {
        if (self::isEnabled()) {
            return self::fetchTemplate('body_tag.tpl');
        }
        return;
    }
}
