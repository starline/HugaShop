<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.6
 * 
 * @link https://ogp.me/
 *
 * Facebook
 * @link https://developers.facebook.com/docs/marketing-api/catalog/reference/#og-tags
 *
 */

namespace HugaShop\Extensions\OpenGraph;

use DateTime;
use DateTimeZone;
use HugaShop\Models\Settings;
use HugaShop\Services\Design;
use HugaShop\Extensions\BaseExtension;
use HugaShop\Models\Finance\FinanceCurrency;

final class OpenGraph extends BaseExtension
{

    /**
     * Get block template
     */
    public static function getFrontHeadTemplate(array $params = [])
    {
        if (self::isEnabled()) {

            if (!Design::getTemplateVars('currency')) {
                Design::assign('currency', FinanceCurrency::getMainCurrency());
            }

            $timezone           = new DateTimeZone(Settings::getParam('timezone'));
            $timezone_offset    = $timezone->getOffset(new DateTime) / 60 / 60;
            $timezone_offset    = $timezone_offset > 0 ? '+' . $timezone_offset : $timezone_offset;
            Design::assign('timezone_offset', $timezone_offset);

            return self::fetchTemplate('graph.tpl');
        }
        return;
    }
}
