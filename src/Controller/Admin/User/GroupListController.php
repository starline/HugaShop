<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.2
 *
 */

namespace App\Controller\Admin\User;

use HugaShop\Api\Design;
use HugaShop\Api\Helper;
use HugaShop\Api\Request;
use HugaShop\Api\User\UserGroup;
use HugaShop\Api\User\UserPermission;
use App\Controller\BaseAdminController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class GroupListController extends BaseAdminController
{
    #[Route('/admin/user/groups', name: 'GroupListAdmin')]
    public function index(): Response
    {

        $this->checkAdminAccess('user_group');

        // Обработка действий
        if (Request::checkCSRF() and UserPermission::checkAccess("user_group_edit")) {

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
                UserGroup::whereId($id)->update(['position' => $position]);
            }
        }

        Design::assign('groups', UserGroup::orderBy('position')->get());

        return $this->fetchResponse('user/group_list.tpl');
    }
}
