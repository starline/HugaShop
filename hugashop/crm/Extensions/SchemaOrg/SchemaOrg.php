<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.5
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
    public function getFrontBodyTemplate()
    {
        if (!empty(self::getSettings()->enabled)) {
            return $this->fetchTemplate('templates/schema.tpl');
        }
        return null;
    }
}
