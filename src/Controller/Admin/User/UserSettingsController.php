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
use HugaShop\Models\User\User;
use HugaShop\Services\Request;
use HugaShop\Models\User\UserGroup;
use HugaShop\Models\User\UserNotifier;
use HugaShop\Services\NotifierFactory;
use App\Controller\BaseAdminController;
use HugaShop\Models\User\UserPermission;
use HugaShop\Models\User\UserNotifierType;
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
        if (!empty($current_user = Request::getInputCheckEditAccess(User::class, $id, exclude: ['name']))) {

            Design::setFlashMessage('update', User::updateUser($current_user->id, $current_user));

            // Update user Permission
            $permissions_arr = Request::post('permissions', 'array');
            UserPermission::updatePermissions($current_user->id, $permissions_arr);

            // Update User Notifier Types
            $user_notifier_types_arr = Request::post('user_notifier_types', 'array');
            UserNotifierType::updateTypes($current_user->id, $user_notifier_types_arr);
        }


        #### View
        #########
        if (empty($current_user = User::getUser($id))) {
            return $this->redirectToRoute('UserListAdmin');
        }

        $permissions            = UserPermission::getUserPermissionsName($current_user->id);
        $notifier_methods       = UserNotifier::getList(['enabled' => 1], order: 'position');
        $notifier_messages      = NotifierFactory::getNotifierMessages('admin');
        $user_allowed_messages  = UserNotifierType::getUserTypes($current_user->id);

        Design::assign('current_user',          $current_user);
        Design::assign('permissions',           $permissions);
        Design::assign('permissions_list',      UserPermission::$permissions_list);
        Design::assign('groups',                UserGroup::getList(order: 'position'));  # Выбираем все группы пользователей
        Design::assign('notifier_messages',     $notifier_messages);
        Design::assign('notifier_methods',      $notifier_methods);
        Design::assign('user_allowed_messages', $user_allowed_messages);

        return $this->fetchResponse('user/user_settings.tpl');
    }
}
