<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 3.0
 *
 * @link https://github.com/facebook/facebook-php-business-sdk
 * Composer require facebook/php-business-sdk
 *
 * Params
 * @link https://developers.facebook.com/docs/marketing-api/conversions-api/parameters/server-event
 *
 * Request Helper
 * @link https://developers.facebook.com/docs/marketing-api/conversions-api/payload-helper?
 * 
 * Pixel Events
 * @link https://developers.facebook.com/docs/meta-pixel/reference
 *
 */

namespace HugaShop\Extensions\FacebookPixel;

use HugaShop\Extensions\BaseExtension;
use HugaShop\Models\Finance\FinanceCurrency;


final class FacebookPixel extends BaseExtension
{
    /**
     * Get block template
     */
    public static function getFrontHeadTemplate()
    {
        if (!empty(self::getSettings()->enabled)) {

            // Set currency
            if (empty(self::getSettings()->currency_code)) {
                self::getSettings()->currency_code = FinanceCurrency::getMainCurrency()->code;
            }
            return self::fetchTemplate('templates/pixel.tpl');
        }
        return;
    }
}
