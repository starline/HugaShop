<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.9
 *
 * Example of Google Data Layer for GA4
 * @link https://enhancedecommerce.appspot.com/
 *
 * Custom Events: view_item_list|select_item|view_item|add_to_wishlist|add_to_cart|remove_from_cart|view_cart|begin_checkout|add_shipping_info|add_payment_info|purchase
 * @link https://developers.google.com/analytics/devguides/collection/ga4/reference/events?client_type=gtm
 */

namespace HugaShop\Extensions\GoogleDataLayerGA4;

use HugaShop\Api\Config;
use HugaShop\Api\Finance\FinanceCurrency;
use HugaShop\Extensions\BaseExtension;

final class GoogleDataLayerGA4 extends BaseExtension
{

    public $cookie_key = 'GDL';

    /**
     * Get block template
     */
    public function getFrontBodyTemplate()
    {
        if (!empty($this->ext_settings->enabled)) {

            // Set currency
            if (empty($this->ext_settings->currency_code)) {
                $this->ext_settings->currency_code = FinanceCurrency::getMainCurrency()->code;
            }

            $this->ext_settings->cookie_key = Config::get('cookie_prefix') . $this->cookie_key;

            return $this->fetchTemplate('datalayer.tpl');
        }

        return null;
    }
}
