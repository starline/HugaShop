<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.5
 *
 * Users Group
 *
 */

namespace App\Controller\Admin\User;

use HugaShop\Services\Design;
use HugaShop\Models\Request;
use HugaShop\Models\User\UserGroup;
use App\Controller\BaseAdminController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class GroupController extends BaseAdminController
{
    #[Route('/admin/user/group', name: 'GroupNewAdmin')]
    #[Route('/admin/user/group/{id}', requirements: ['id' => '\d+'], name: 'GroupAdmin')]
    public function index(?int $id = null): Response
    {

        $this->checkAdminAccess('user_group_edit');

        #### Update
        ###########
        if (!empty($group = Request::getDataAcces(UserGroup::getFields()))) {

            if (empty($group->id)) {
                $group = Design::setFlashMessage('add', UserGroup::createOne($group));
            } else {
                Design::setFlashMessage('update', UserGroup::whereId($group->id)->updateOne($group));
            }

            return $this->redirectToRoute('GroupAdmin', ['id' => $group->id]);
        }

        #### View
        #########
        if (!empty($id)) {
            $group = UserGroup::find($id);

            if (empty($group->id)) {
                return $this->redirectToRoute('GroupListAdmin');
            }
        }

        Design::assign('group', $group);

        return $this->fetchResponse('user/group.tpl');
    }
}
