<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.4
 *
 */

namespace App\Controller\Admin\User;

use HugaShop\Models\User\User;
use HugaShop\Services\Design;
use HugaShop\Services\Request;
use HugaShop\Models\User\UserGroup;
use HugaShop\Models\User\UserNotifier;
use HugaShop\Models\User\UserPermission;
use App\Controller\BaseAdminController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class UserSettingsController extends BaseAdminController
{

    #[Route('/admin/user/{id}/settings', requirements: ['id' => '\d+'], name: 'UserSettingsAdmin')]
    public function index(int $id): Response
    {

        $this->checkAdminAccess('user_settings');


        #### Update
        ###########
        if (!empty($current_user = Request::getInputCheckEditAccess(User::class, $id))) {

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

        $permissions            = UserPermission::getUserPermissionsName($current_user->id);
        $notifier_methods       = UserNotifier::getList(['enabled' => 1], order: 'position');
        $notifier_types         = UserNotifier::getNotifierTypes('admin');
        $user_notifier_types    = UserNotifier::getUserNotifierTypes($current_user->id);

        Design::assign([
            'current_user'          => $current_user,
            'permissions'           => $permissions,
            'permissions_list'      => UserPermission::$permissions_list,
            'groups'                => UserGroup::getList(order: 'position'),  # Выбираем все группы пользователей
            'notifier_types'        => $notifier_types,
            'notifier_methods'      => $notifier_methods,
            'user_notifier_types'   => $user_notifier_types
        ]);

        return $this->fetchResponse('user/user_settings.tpl');
    }
}
