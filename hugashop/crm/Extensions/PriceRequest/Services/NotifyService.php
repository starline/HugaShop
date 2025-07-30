<?php
/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.0
 */

namespace HugaShop\Extensions\PriceRequest\Services;

use HugaShop\Services\Design;
use HugaShop\Extensions\BaseExtensionTrait;

final class NotifyService
{
    use BaseExtensionTrait;

    /**
     * Email message to admin
     */
    public static function requestToAdmin(string $module_name, array &$message_data)
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
