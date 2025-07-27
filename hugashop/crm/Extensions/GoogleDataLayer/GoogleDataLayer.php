<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.7
 * @link https://enhancedecommerce.appspot.com/
 *
 */

namespace HugaShop\Extensions\GoogleDataLayer;

use HugaShop\Models\Finance\FinanceCurrency;
use HugaShop\Extensions\BaseExtension;

final class GoogleDataLayer extends BaseExtension
{

    /**
     * Get block template
     */
    public static function getFrontBodyTemplate(): ?string
    {
        if (empty(self::getSettings()->enabled)) {
            return null;
        }

        if (empty(self::getSettings()->currency_code)) {
            self::getSettings()->currency_code = FinanceCurrency::getMainCurrency()->code;
        }

        return self::fetchTemplate('datalayer.tpl');
    }
}
