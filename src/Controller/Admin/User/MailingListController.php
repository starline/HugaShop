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
use HugaShop\Services\Request;
use App\Services\PaginationService;
use HugaShop\Models\User\UserMailing;
use App\Controller\BaseAdminController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MailingListController extends BaseAdminController
{
    #[Route('/admin/user/mailings', name: 'MailingListAdmin')]
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
                            UserMailing::deleteOne($ids);
                            break;
                        }
                }
            }
        }

        $filter = PaginationService::initFilter();

        $mailing_list = UserMailing::getList($filter, order: ['id', 'DESC'], join: ['user', 'notifier']);
        $mailing_count = UserMailing::getCount($filter);

        Design::assign('pagination', PaginationService::getPagination($mailing_count, $filter));

        Design::assign('mailing_count', $mailing_count);
        Design::assign('mailing_list', $mailing_list);

        return $this->fetchResponse('user/mailing_list.tpl');
    }
}
