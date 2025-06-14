<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.3
 *
 * Отображение статей на сайте
 * Этот класс использует шаблоны articles.tpl и article.tpl
 *
 */

namespace App\Controller\Front;

use HugaShop\Api\Design;
use HugaShop\Api\Request;
use HugaShop\Api\User\UserNotifier;
use HugaShop\Api\Content\ContentFeedback;
use App\Controller\BaseFrontController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class FeedbackController extends BaseFrontController
{

    #[Route('/feedback', name: 'Feedback', priority: 1)]
    public function feedback(): Response
    {
        $feedback = new \stdClass();

        if (Request::checkCSRF() && Request::post('feedback')) {

            $feedback->name         = Request::post('name');
            $feedback->email        = Request::post('email');
            $feedback->message      = Request::post('message');

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

                // Отправляем email
                UserNotifier::sendNotifierToManager('feedbackToAdmin', [
                    'feedback_id' => $feedback_id
                ]);
            }
        }

        return $this->fetchResponse('feedback.tpl');
    }
}
