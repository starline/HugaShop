<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.1
 */

namespace HugaShop\Addons\ProductPriceRequest\Services;

use HugaShop\Services\Design;
use HugaShop\Addons\BaseAddonTrait;

final class NotifyService
{
    use BaseAddonTrait;

    /**
     * Email message to admin
     */
    public static function priceRequestToAdmin(string $module_name, array &$message_data)
    {
        if (empty($message_data['request']) || empty($message_data['product'])) {
            return;
        }

        $template_path = self::getTemplatePath(strtolower($module_name) . '_admin.tpl');
        if (!file_exists($template_path)) {
            return;
        }

        Design::assign('request', $message_data['request']);
        Design::assign('product', $message_data['product']);

        $message                 = Design::fetch($template_path);
        $message_data['subject'] = Design::getTemplateVars('subject');

        return $message;
    }
}
