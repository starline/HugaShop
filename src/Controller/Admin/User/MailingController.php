<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.5
 *
 */

namespace App\Controller\Admin\User;

use HugaShop\Services\Design;
use HugaShop\Services\Secure;
use HugaShop\Services\Request;
use HugaShop\Services\DesignTwig;
use HugaShop\Models\User\UserMailing;
use HugaShop\Models\User\UserNotifier;
use App\Controller\BaseAdminController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MailingController extends BaseAdminController
{
    #[Route('/admin/user/mailing', name: 'MailingNewAdmin')]
    #[Route('/admin/user/mailing/{id}', requirements: ['id' => '\d+'], name: 'MailingAdmin')]
    public function index(?int $id = null): Response
    {

        $this->checkAdminAccess('user_notifier');


        #### Update
        ###########
        if (!empty($mailing = Secure::getInputCheckEditAccess(UserMailing::class, $id))) {
            if (empty($mailing->id)) {
                $mailing->id = Design::setFlashMessage('add', UserMailing::addMailing($mailing));
            } else {
                Design::setFlashMessage('update', UserMailing::updateOne($mailing->id, $mailing));
            }

            if (Request::post('action') === 'send') {
                UserMailing::sendOne($mailing->id);
            }

            // Делаем редирект на страницу с ID
            return $this->redirectToRoute('MailingAdmin', ['id' => $mailing->id]);
        }


        #### View
        #########
        if (!empty($id)) {
            $mailing = UserMailing::getOne(intval($id), ['user', 'template']);
            if (empty($mailing->id)) {
                return $this->redirectToRoute('MailingListAdmin');
            }

            if (!empty($mailing->template->content)) {
                $template_params['utm_link'] = UserMailing::makeShortUTMLink($mailing);
                $mailing->template->compiled = DesignTwig::renderTemplate($mailing->template->content, $template_params);
            }
        }

        Design::assign('mailing', $mailing);
        Design::assign('notifiers', UserNotifier::getList(['enabled' => 1], order: 'position'));

        return $this->fetchResponse('user/mailing.tpl');
    }
}
