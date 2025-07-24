<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.1
 */

namespace HugaShop\Extensions\Feedback\Controller;

use HugaShop\Services\Design;
use HugaShop\Services\Helper;
use HugaShop\Services\Request;
use HugaShop\Models\User\UserNotifier;
use App\Controller\BaseFrontController;
use HugaShop\Extensions\BaseExtensionTrait;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use HugaShop\Extensions\Feedback\Models\Feedback;

final class FeedbackController extends BaseFrontController
{
    use BaseExtensionTrait;

    #[Route('/feedback', name: 'ExtFeedback', priority: 1)]
    public function feedback(): Response
    {
        if (Request::checkCSRF()) {
            $feedback = new \stdClass();
            $feedback->name    = Request::post('name');
            $feedback->email   = Request::post('email');
            $feedback->message = Request::post('message');

            Design::assign('name', $feedback->name);
            Design::assign('email', $feedback->email);
            Design::assign('message', $feedback->message);

            if (empty($feedback->name)) {
                Design::append('form_invalid', 'name');
            } elseif (empty($feedback->email)) {
                Design::append('form_invalid', 'email');
            } elseif (empty($feedback->message)) {
                Design::append('form_invalid', 'text');
            } elseif (!Helper::checkCaptcha()) {
                Design::assign('error', 'captcha');
            } else {
                Design::assign('message_sent', true);

                $feedback->ip = $_SERVER['REMOTE_ADDR'];
                $feedback = Feedback::createOne($feedback);

                //UserNotifier::sendNotifierToManager(null, ['feedback' => $feedback], 'feedback_admin_notify');
            }
        }

        return $this->fetchExtResponse('feedback.tpl');
    }

    /**
     * Send Feedback to Admin
     *
     * @param string $template_path
     * @param array $message_params
     */
    private function feedbackToAdmin(string $template_path, array &$message_params)
    {
        if (empty($message_params['feedback_id']) || empty($feedback = Feedback::getOne(intval($message_params['feedback_id'])))) {
            return false;
        }

        Design::assign([
            'feedback' => $feedback
        ]);

        // Image template
        $template = Design::fetch($template_path);
        $message_params['subject'] = Design::getTemplateVars('subject');

        return $template;
    }
}
