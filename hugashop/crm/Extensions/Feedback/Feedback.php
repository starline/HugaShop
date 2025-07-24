<?php
/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.0
 */

namespace HugaShop\Extensions\Feedback;

use HugaShop\Extensions\BaseExtension;

final class Feedback extends BaseExtension
{
    /**
     * Get block template
     */
    public static function getTemplate(array $params = [])
    {
        return self::fetchTemplate('templates/feedback.tpl');
    }
}
