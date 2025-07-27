<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.0
 *
 * Example of Google Data Layer for GA4
 * @link https://enhancedecommerce.appspot.com/
 *
 * Custom Events: view_item_list|select_item|view_item|add_to_wishlist|add_to_cart|remove_from_cart|view_cart|begin_checkout|add_shipping_info|add_payment_info|purchase
 * @link https://developers.google.com/analytics/devguides/collection/ga4/reference/events?client_type=gtm
 */

namespace HugaShop\Extensions\GoogleDataLayerGA4;

use HugaShop\Services\Config;
use HugaShop\Models\Finance\FinanceCurrency;
use HugaShop\Extensions\BaseExtension;

final class GoogleDataLayerGA4 extends BaseExtension
{

    private static $cookie_key = 'GDL';

    /**
     * Get block template
     */
    public static function getFrontBodyTemplate()
    {
        if (!empty(self::getSettings()->enabled)) {

            // Set currency
            if (empty(self::getSettings()->currency_code)) {
                self::getSettings()->currency_code = FinanceCurrency::getMainCurrency()->code;
            }

            self::getSettings()->cookie_key = Config::get('cookie_prefix') . self::$cookie_key;

            return self::fetchTemplate('datalayer.tpl');
        }

        return null;
    }
}
