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
use HugaShop\Models\User\UserMailTemplate;
use App\Controller\BaseAdminController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MailTemplateListController extends BaseAdminController
{
    #[Route('/admin/user/mailing/templates', name: 'MailTemplateListAdmin')]
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
                            UserMailTemplate::deleteOne($ids);
                            break;
                        }
                }
            }
        }

        $filter = PaginationService::initFilter();

        $mail_template_list =   UserMailTemplate::getList($filter, order: ['id', 'DESC']);
        $mail_template_count =  UserMailTemplate::getCount($filter);

        Design::assign('pagination', PaginationService::getPagination($mail_template_count, $filter));

        Design::assign('mail_template_count', $mail_template_count);
        Design::assign('mail_template_list', $mail_template_list);

        return $this->fetchResponse('user/mail_template_list.tpl');
    }
}
