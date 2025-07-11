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
        dump(self::getSettings());
        if (!empty(self::getSettings()->enabled)) {
            return self::fetchTemplate('templates/schema.tpl');
        }
        return null;
    }
}
