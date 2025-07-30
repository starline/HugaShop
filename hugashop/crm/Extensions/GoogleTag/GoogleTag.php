<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.6
 *
 */

namespace HugaShop\Extensions\GoogleTag;

use HugaShop\Extensions\BaseExtension;

final class GoogleTag extends BaseExtension
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
