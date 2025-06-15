<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.0
 *
 */

namespace App\Controller\Admin\User;

use HugaShop\Api\Design;
use HugaShop\Api\Request;
use HugaShop\Api\Settings;
use HugaShop\Api\User\UserMailing;
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

        $filter = [];
        $filter['page'] =  max(1, Request::get('page', 'int'));
        $filter['limit'] = Request::get('page', 'string') == 'all' ? 'all' : Settings::getParam('products_num_admin');

        $mailing_list = UserMailing::getList($filter, order: ['id', 'DESC'], join: ['user', 'notifier']);
        $mailing_count = UserMailing::getCount($filter);

        Design::assign('pages_count', ceil($mailing_count / Settings::getParam('products_num_admin')));
        Design::assign('current_page', $filter['limit'] == 'all' ? 'all' : $filter['page']);

        Design::assign('mailing_count', $mailing_count);
        Design::assign('mailing_list', $mailing_list);

        return $this->fetchResponse('user/mailing_list.tpl');
    }
}
