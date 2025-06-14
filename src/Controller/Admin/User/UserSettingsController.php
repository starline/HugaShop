<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.1
 *
 */

namespace App\Controller\Admin\User;

use HugaShop\Api\User\User;
use HugaShop\Api\Design;
use HugaShop\Api\Request;
use HugaShop\Api\User\UserGroup;
use HugaShop\Api\User\UserNotifier;
use HugaShop\Api\User\UserPermission;
use App\Controller\BaseAdminController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class UserSettingsController extends BaseAdminController
{
    private $entity_params = [
        'id' =>         ['type' => 'int'],
        'group_id' =>   ['type' => 'int', 'access' => 'order_edit']
    ];


    #[Route('/admin/user/{id}/settings', requirements: ['id' => '\d+'], name: 'UserSettingsAdmin')]
    public function index(int $id): Response
    {

        $this->checkAdminAccess('user_settings');


        #### Update
        ###########
        if (!empty($current_user = Request::getDataAcces($this->entity_params))) {

            Design::setFlashMessage('update', User::updateUser($current_user->id, $current_user));

            // Update user Permission
            $permissions_arr = Request::post('permissions', 'array');
            UserPermission::updatePermissions($current_user->id, $permissions_arr);

            // Update User Notifier Types
            $user_notifier_types_arr = Request::post('user_notifier_types', 'array');
            UserNotifier::updateUserNotifierTypes($current_user->id, $user_notifier_types_arr);
        }


        #### View
        #########
        if (empty($current_user = User::getUser($id))) {
            return $this->redirectToRoute('UserListAdmin');
        }

        $permissions = UserPermission::getUserPermissionsName($current_user->id);
        $groups = UserGroup::orderBy('position')->get(); # Выбираем все группы пользователей

        $notifier_methods =     UserNotifier::getList(['enabled' => 1], order: 'position');
        $notifier_types =       UserNotifier::getNotifierTypes('admin');
        $user_notifier_types =  UserNotifier::getUserNotifierTypes($current_user->id);

        Design::assign([
            'current_user' => $current_user,
            'permissions' => $permissions,
            'permissions_list' => UserPermission::$permissions_list,
            'groups' => $groups,
            'notifier_types' => $notifier_types,
            'notifier_methods' => $notifier_methods,
            'user_notifier_types' => $user_notifier_types
        ]);

        return $this->fetchResponse('user/user_settings.tpl');
    }
}
