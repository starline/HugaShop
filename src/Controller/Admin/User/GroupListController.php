<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.4
 *
 */

namespace App\Controller\Admin\User;

use HugaShop\Services\Design;
use HugaShop\Services\Helper;
use HugaShop\Services\Secure;
use HugaShop\Services\Request;
use HugaShop\Models\User\UserGroup;
use App\Controller\BaseAdminController;
use HugaShop\Models\User\UserPermission;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class GroupListController extends BaseAdminController
{
    #[Route('/admin/user/groups', name: 'GroupListAdmin')]
    public function index(): Response
    {

        $this->checkAdminAccess('user_group');

        // Обработка действий
        if (Secure::checkCSRF() and UserPermission::checkAccess("user_group_edit")) {

            // Действия с выбранными
            $ids = Request::post('check');
            if (is_array($ids)) {
                switch (Request::post('action')) {
                    case 'delete': {
                            foreach ($ids as $id) {
                                Design::setFlashMessage('delete', UserGroup::deleteGroup($id));
                            }
                            break;
                        }
                }
            }

            foreach (Helper::getPositions() as $id => $position) {
                UserGroup::updateOne($id, ['position' => $position]);
            }
        }

        Design::assign('groups', UserGroup::getList(order: 'position'));

        return $this->fetchResponse('user/group_list.tpl');
    }
}
