<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.2
 *
 */

namespace App\Controller\Admin\User;

use HugaShop\Services\Design;
use HugaShop\Services\Secure;
use HugaShop\Models\User\User;
use HugaShop\Services\Request;
use App\Services\PaginationService;
use HugaShop\Models\User\UserGroup;
use App\Controller\BaseAdminController;
use HugaShop\Models\User\UserPermission;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class UserListController extends BaseAdminController
{
    #[Route('/admin/users', name: 'UserListAdmin')]
    public function index(): Response
    {

        $this->checkAdminAccess('user');

        if (Secure::checkCSRF() and UserPermission::checkAccess("user_edit")) {

            // Действия с выбранными
            $ids = Request::post('check');
            if (is_array($ids)) {
                switch (Request::post('action')) {
                    case 'disable': {
                            foreach ($ids as $id) {
                                User::updateUser($id, ['enabled' => 0]);
                            }
                            break;
                        }
                    case 'enable': {
                            foreach ($ids as $id) {
                                User::updateUser($id, ['enabled' => 1]);
                            }
                            break;
                        }
                    case 'delete': {
                            foreach ($ids as $id) {
                                User::deleteUser($id);
                            }
                            break;
                        }
                }
            }
        }

        $filter = PaginationService::initFilter();

        // Ограничеваем просмотр списка только 1-й страницей
        if (!UserPermission::checkAccess('user_edit')) {
            $filter['page'] = 1;
            Design::assign('pagination_hide', true);
        }

        if (!empty($group_id = Request::getInt('group_id'))) {
            $group = UserGroup::find($group_id);
            $filter['group_id'] = $group->id;
            Design::assign('group', $group);
        }

        // Показать сотрудников
        $manager = Request::get('manager');
        if (!empty($manager) and UserPermission::checkAccess('user_manager')) {
            $filter['manager'] = $manager;
            Design::assign('manager', $manager);
        }

        // Поиск
        $keyword = Request::get('keyword');
        if (!empty($keyword)) {
            $filter['search'] = $keyword;
            Design::assign('keyword', $keyword);
        }

        // Сортировка
        $sort = Request::get('sort', 'string', 'name');

        $users          = User::getUsers($filter, order: $sort);
        $users_count    = User::getCount($filter);
        $groups         = UserGroup::orderBy('position')->get();

        Design::assign('pagination',    PaginationService::getPagination($users_count, $filter));
        Design::assign('groups',        $groups);
        Design::assign('users',         $users);
        Design::assign('users_count',   $users_count);
        Design::assign('sort',          $sort);

        return $this->fetchResponse('user/user_list.tpl');
    }
}
