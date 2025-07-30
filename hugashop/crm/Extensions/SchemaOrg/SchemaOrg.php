<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.6
 * 
 * @link https://schema.org/
 *
 */

namespace HugaShop\Extensions\SchemaOrg;

use HugaShop\Extensions\BaseExtension;

final class SchemaOrg extends BaseExtension
{
    /**
     * Get block template
     */
    public static function getFrontBodyTemplate()
    {
        if (self::isEnabled()) {
            return self::fetchTemplate('schema.tpl');
        }
        return;
    }
}
