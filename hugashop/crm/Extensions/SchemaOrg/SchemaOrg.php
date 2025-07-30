<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.7
 * 
 * @link https://schema.org/
 *
 */

namespace HugaShop\Extensions\SchemaOrg;

use HugaShop\Services\Design;
use HugaShop\Extensions\BaseExtension;
use HugaShop\Models\Finance\FinanceCurrency;

final class SchemaOrg extends BaseExtension
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
