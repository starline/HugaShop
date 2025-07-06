<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.3
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
use HugaShop\Services\Design;
use HugaShop\Models\Settings;
use HugaShop\Extensions\BaseExtension;

final class OpenGraph extends BaseExtension
{

    /**
     * Get block template
     */
    public function getFrontHeadTemplate(array $params = [])
    {
        $timezone = new DateTimeZone(Settings::getParam('timezone'));
        $timezone_offset = $timezone->getOffset(new DateTime) / 60 / 60;
        $timezone_offset = $timezone_offset > 0 ? '+' . $timezone_offset : $timezone_offset;
        Design::assign('timezone_offset', $timezone_offset);

        if (!empty($this->settings->enabled)) {
            return $this->fetchTemplate('graph.tpl');
        }
        return null;
    }
}
