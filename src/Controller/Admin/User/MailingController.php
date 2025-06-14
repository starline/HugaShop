<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.1
 *
 */

namespace App\Controller\Admin\User;

use HugaShop\Api\Design;
use HugaShop\Api\Request;
use HugaShop\Api\DesignTwig;
use HugaShop\Api\User\UserMailing;
use HugaShop\Api\User\UserNotifier;
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

        $notifiers = UserNotifier::getList(['enabled' => 1], order: 'position');


        #### Update
        ###########
        if (!empty($mailing = Request::getDataAcces(UserMailing::$table_fields))) {

            if (empty($mailing->id)) {
                $mailing->id = Design::setFlashMessage('add', UserMailing::addMailing($mailing));
            } else {
                UserMailing::updateOne($mailing->id, $mailing);
            }

            if (!empty(Request::post('action_send'))) {
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
        Design::assign('notifiers', $notifiers);

        return $this->fetchResponse('user/mailing.tpl');
    }
}
