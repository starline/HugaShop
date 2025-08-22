<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.8
 * 
 * @link https://schema.org/
 *
 */

namespace HugaShop\Addons\SchemaOrg;

use HugaShop\Services\Design;
use HugaShop\Addons\BaseAddon;
use HugaShop\Models\Finance\FinanceCurrency;

final class SchemaOrg extends BaseAddon
{
    /**
     * Get block template
     */
    public static function getFrontBodyTemplate()
    {
        if (self::isEnabled()) {
            if (!Design::getTemplateVars('currency')) {
                Design::assign('currency', FinanceCurrency::getMainCurrency());
            }
            return self::fetchTemplate('schema.tpl');
        }
        return;
    }
}
