<?php

/**
 * HugaShop - Sell anything
 * 
 * @author Andri Huga
 * @version 1.7
 *
 */

namespace HugaShop\Extensions\Feedback\Services;

use HugaShop\Services\Design;
use HugaShop\Extensions\BaseExtensionTrait;


final class NotifyService
{

    use BaseExtensionTrait;

    /**
     * Send Feedback to Admin
     *
     * @param string $template_path
     * @param array $message_data
     */
    public static function feedbackToAdmin(string $module_name, array &$message_data)
    {

        if (empty($message_data['feedback'])) {
            return;
        }

        $template_path = self::getTemplatePath(strtolower($module_name) . '_feedbackToAdmin.tpl');
        
        if (!file_exists($template_path)) {
            return;
        }

        // Image template
        Design::assign('feedback', $message_data['feedback']);

        $message                  = Design::fetch($template_path);
        $message_data['subject']  = Design::getTemplateVars('subject');

        return $message;
    }
}
