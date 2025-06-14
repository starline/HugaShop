<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.4
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
        if (!empty($this->ext_settings->enabled)) {
            return $this->fetchTemplate('schema.tpl');
        }
        return null;
    }
}
