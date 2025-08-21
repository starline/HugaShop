<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.0
 */

namespace HugaShop\Extensions\HelpOffer\Services;

use HugaShop\Services\Design;
use HugaShop\Extensions\BaseExtensionTrait;

final class NotifyService
{
    use BaseExtensionTrait;

    /**
     * Email message to admin
     */
    public static function offerToAdmin(string $module_name, array &$message_data)
    {
        if (empty($message_data['request'])) {
            return;
        }

        $template_path = self::getTemplatePath(strtolower($module_name) . '_offerToAdmin.tpl');
        if (!file_exists($template_path)) {
            return;
        }

        Design::assign('request', $message_data['request']);

        $message                 = Design::fetch($template_path);
        $message_data['subject'] = Design::getTemplateVars('subject');

        return $message;
    }
}
