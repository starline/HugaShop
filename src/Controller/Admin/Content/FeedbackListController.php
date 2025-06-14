<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.1
 *
 */

namespace App\Controller\Admin\Content;

use HugaShop\Api\Design;
use HugaShop\Api\Request;
use HugaShop\Api\Settings;
use HugaShop\Api\Content\ContentFeedback;
use App\Controller\BaseAdminController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class FeedbackListController extends BaseAdminController
{
    #[Route('/admin/feedbacks', name: 'FeedbackListAdmin')]
    public function index(): Response
    {

        $this->checkAdminAccess('feedback');

        // Поиск
        $keyword = Request::get('keyword', 'string');
        if (!empty($keyword)) {
            $filter['keyword'] = $keyword;
            Design::assign('keyword', $keyword);
        }

        // Обработка действий
        if (Request::checkCSRF()) {

            // Действия с выбранными
            $ids = Request::post('check');
            if (!empty($ids)) {
                switch (Request::post('action')) {
                    case 'delete': {
                            ContentFeedback::deleteOne($ids);
                            break;
                        }
                }
            }
        }

        // Отображение
        $filter = [];
        $filter['page'] = max(1, Request::get('page', 'int'));
        $filter['limit'] = Request::get('page', 'string') == 'all' ? 'all' : Settings::getParam('products_num_admin');

        // Поиск
        $keyword = Request::get('keyword', 'string');
        if (!empty($keyword)) {
            $filter['keyword'] = $keyword;
            Design::assign('keyword', $keyword);
        }

        $feedbacks_count = ContentFeedback::countFeedbacks($filter);
        $feedbacks = ContentFeedback::getFeedbacks($filter, true);

        Design::assign('pages_count', ceil($feedbacks_count / Settings::getParam('products_num_admin')));
        Design::assign('current_page', $filter['limit'] == 'all' ? 'all' : $filter['page']);
        Design::assign('feedbacks', $feedbacks);
        Design::assign('feedbacks_count', $feedbacks_count);

        return $this->fetchResponse('content/feedback_list.tpl');
    }
}
