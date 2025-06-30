<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.3
 *
 * User Notyfi
 *
 */

namespace App\Controller\Admin\User;

use HugaShop\Services\Design;
use HugaShop\Models\Request;
use HugaShop\Models\User\UserNotifier;
use App\Controller\BaseAdminController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class NotifierController extends BaseAdminController
{
    #[Route('/admin/user/notifier', name: 'NotifierNewAdmin')]
    #[Route('/admin/user/notifier/{id}', requirements: ['id' => '\d+'], name: 'NotifierAdmin')]
    public function index(?int $id = null): Response
    {

        $this->checkAdminAccess('user_notifier');

        $notifier_settings = [];
        $notifier_modules = UserNotifier::getNotifierModules();

        #### Update
        ###########
        if (!empty($notifier = Request::getDataAcces(UserNotifier::getFields()))) {

            $notifier->settings = Request::post('notifier_settings');

            // Set type
            if (!empty($notifier->module)) {
                $notifier->type = $notifier_modules[$notifier->module]->type;
            }

            if (empty($notifier->id)) {
                $notifier = Design::setFlashMessage('add', UserNotifier::createOne($notifier));
            } else {
                Design::setFlashMessage('update', UserNotifier::updateOne($notifier->id, $notifier));
            }

            // Делаем редирект на страницу с ID
            return $this->redirectToRoute('NotifierAdmin', ['id' => $notifier->id]);
        }


        #### View
        #########
        if (!empty($id)) {

            $notifier = UserNotifier::getOne($id);

            if (empty($notifier->id)) {
                return $this->redirectToRoute('NotifierListAdmin');
            }

            $notifier_settings = $notifier->settings;
        }

        Design::assign('notifier', $notifier);
        Design::assign('notifier_modules', $notifier_modules);
        Design::assign('notifier_settings', $notifier_settings);

        return $this->fetchResponse('user/notifier.tpl');
    }
}
