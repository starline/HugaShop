<?php

/**
 * HugaShop - Sell anything
 * 
 * @author Andri Huga
 * @version 1.7
 *
 */

namespace HugaShop\Extensions\Feedback\Services;

use HugaShop\Extensions\BaseExtension;
use HugaShop\Services\Design;


final class NotifyService extends BaseExtension
{

    /**
     * Send Feedback to Admin
     *
     * @param string $template_path
     * @param array $message_params
     */
    public static function feedbackToAdmin(string $module_name, array $message_data)
    {

        if (empty($message_data['feedback'])) {
            return false;
        }

        Design::assign('feedback', $message_data['feedback']);

        $template_path = self::getTemplatePath($module_name . '_feedback_admin' . '.tpl');
        if (!file_exists($template_path)) {
            return;
        }

        // Image template
        $message                    = Design::fetch($template_path);
        $message_params['subject']  = Design::getTemplateVars('subject');

        return $message;
    }
}
