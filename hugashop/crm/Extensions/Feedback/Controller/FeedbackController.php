<?php
/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.0
 */

namespace HugaShop\Extensions\Feedback\Controller;

use HugaShop\Services\Design;
use HugaShop\Services\Request;
use HugaShop\Models\User\UserNotifier;
use HugaShop\Models\Content\ContentFeedback;
use App\Controller\BaseFrontController;
use HugaShop\Extensions\BaseExtensionTrait;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

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
            } else {
                Design::assign('message_sent', true);

                $feedback->ip = $_SERVER['REMOTE_ADDR'];
                $feedback_id = ContentFeedback::addFeedback($feedback);

                UserNotifier::sendNotifierToManager('feedbackToAdmin', [
                    'feedback_id' => $feedback_id,
                ]);
            }
        }

        return $this->fetchExtResponse('feedback.tpl');
    }
}
