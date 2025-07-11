<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.6
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
    public function getFrontBodyTemplate(): ?string
    {
        if (empty($this->settings->enabled)) {
            return null;
        }

        if (empty($this->settings->currency_code)) {
            $this->settings->currency_code = FinanceCurrency::getMainCurrency()->code;
        }

        return $this->fetchTemplate('templates/datalayer.tpl');
    }
}
