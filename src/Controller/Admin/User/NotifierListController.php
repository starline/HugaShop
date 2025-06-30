<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.1
 *
 */

namespace App\Controller\Admin\User;

use HugaShop\Services\Design;
use HugaShop\Models\Helper;
use HugaShop\Models\Request;
use HugaShop\Models\User\UserNotifier;
use App\Controller\BaseAdminController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class NotifierListController extends BaseAdminController
{
    #[Route('/admin/user/notifiers', name: 'NotifierListAdmin')]
    public function index(): Response
    {
        $this->checkAdminAccess('user_notifier');

        // Обработка действий
        if (Request::checkCSRF()) {

            // Действия с выбранными
            $ids = Request::post('check');
            if (is_array($ids)) {
                switch (Request::post('action')) {
                    case 'delete': {
                            foreach ($ids as $id) {
                                UserNotifier::deleteNotifier($id);
                            }
                            break;
                        }
                }
            }

            foreach (Helper::getPositions() as $id => $position) {
                UserNotifier::updateOne($id, ['position' => $position]);
            }
        }

        Design::assign('notifier_list', UserNotifier::getList(order: 'position'));

        return $this->fetchResponse('user/notifier_list.tpl');
    }
}
