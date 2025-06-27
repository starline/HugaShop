<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.2
 *
 */

namespace App\Controller\Admin\Content;

use HugaShop\Models\Design;
use HugaShop\Models\Request;
use App\Services\PaginationService;
use HugaShop\Models\Content\ContentFeedback;
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
        $filter = PaginationService::initFilter();

        // Поиск
        $keyword = Request::get('keyword', 'string');
        if (!empty($keyword)) {
            $filter['keyword'] = $keyword;
            Design::assign('keyword', $keyword);
        }

        $feedbacks_count = ContentFeedback::countFeedbacks($filter);
        $feedbacks = ContentFeedback::getFeedbacks($filter, true);

        Design::assign('pagination', PaginationService::getPagination($feedbacks_count, $filter));
        Design::assign('feedbacks', $feedbacks);
        Design::assign('feedbacks_count', $feedbacks_count);

        return $this->fetchResponse('content/feedback_list.tpl');
    }
}
