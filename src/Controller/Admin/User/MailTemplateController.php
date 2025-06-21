<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.0
 *
 */

namespace App\Controller\Admin\User;

use HugaShop\Api\Design;
use HugaShop\Api\Request;
use HugaShop\Api\User\UserMailTemplate;
use App\Controller\BaseAdminController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MailTemplateController extends BaseAdminController
{
    #[Route('/admin/user/mailing/template', name: 'MailTemplateNewAdmin')]
    #[Route('/admin/user/mailing/template/{id}', requirements: ['id' => '\d+'], name: 'MailTemplateAdmin')]
    public function index(?int $id = null): Response
    {

        $this->checkAdminAccess('user_notifier');

        #### Update
        ###########
        if (!empty($mail_template = Request::getDataAcces(UserMailTemplate::getFields()))) {

            if (empty($mail_template->id)) {
                $mail_template = Design::setFlashMessage('add', UserMailTemplate::create($mail_template));
            } else {
                UserMailTemplate::updateOne($mail_template->id, $mail_template);
            }

            // Делаем редирект на страницу с ID
            return $this->redirectToRoute('MailTemplateAdmin', ['id' => $mail_template->id]);
        }


        #### View
        #########
        if (!empty($id)) {

            $mail_template = UserMailTemplate::getOne($id);

            if (empty($mail_template->id)) {
                return $this->redirectToRoute('MailTemplateListAdmin');
            }
        }

        $notifier_types = UserMailTemplate::$mail_types;

        Design::assign('mail_template', $mail_template);
        Design::assign('notifier_types', $notifier_types);

        return $this->fetchResponse('user/mail_template.tpl');
    }
}
