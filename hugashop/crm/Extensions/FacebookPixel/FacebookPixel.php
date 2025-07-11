<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.9
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
    public function getFrontHeadTemplate()
    {
        if (!empty($this->settings->enabled)) {

            // Set currency
            if (empty($this->settings->currency_code)) {
                $this->settings->currency_code = FinanceCurrency::getMainCurrency()->code;
            }
            return $this->fetchTemplate('templates/pixel.tpl');
        }
        return;
    }
}
