<?php

/**
 *
 * @author Andi Huga
 * @version 1.1
 *
 */

namespace App\Controller\Front\Exchange;

use HugaShop\Services\Config;
use HugaShop\Models\User\UserMailing;
use App\Controller\BaseFrontController;
use Symfony\Component\Routing\Attribute\Route;

class MailingController extends BaseFrontController
{
    #[Route('/m{id}/{token}', requirements: ['id' => '\d+'], name: 'MailingExchange', priority: 10)]
    public function index(int $id, string $token)
    {
        if (empty($id) || empty($token)) {
            throw $this->createNotFoundException('Access denied'); # 404
        }

        if (empty($mail = UserMailing::getOne(['id' => $id, 'token' => $token]))) {
            throw $this->createNotFoundException('Access denied'); # 404
        }

        // Обновить кол-во
        $update_mail = new \stdClass();
        $update_mail->count = $mail->count;
        $update_mail->count++;
        $update_mail->ip = $_SERVER['REMOTE_ADDR']; # IP

        UserMailing::updateOne($id, values: $update_mail);

        // landing link
        if (empty($mail->settings->landing_url)) {
            $redirect_link = Config::get('root_url');
        } else {
            $redirect_link = $mail->settings->landing_url;
        }

        // Редирект
        return $this->redirect($redirect_link, '301');
    }
}
