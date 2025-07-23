<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.4
 *
 */

namespace App\Controller\Admin\Content;

use HugaShop\Services\Design;
use HugaShop\Services\Request;
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
            $filter['search'] = $keyword;
            Design::assign('keyword', $keyword);
        }

        $feedbacks          = ContentFeedback::getList($filter, order: 'id');
        $feedbacks_count    = ContentFeedback::getCount($filter);

        Design::assign('pagination', PaginationService::getPagination($feedbacks_count, $filter));
        Design::assign('feedbacks', $feedbacks);
        Design::assign('feedbacks_count', $feedbacks_count);

        return $this->fetchResponse('content/feedback_list.tpl');
    }
}
