<?php
/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.0
 */

namespace HugaShop\Extensions\Feedback\Controller;

use HugaShop\Services\Design;
use HugaShop\Services\Request;
use App\Services\PaginationService;
use HugaShop\Models\Content\ContentFeedback;
use App\Controller\BaseAdminController;
use HugaShop\Extensions\BaseExtensionTrait;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class FeedbackListController extends BaseAdminController
{
    use BaseExtensionTrait;

    #[Route('/Feedback', name: 'ExtFeedbackList', priority: 20)]
    public function index(): Response
    {
        $this->checkAdminAccess('feedback');

        // \u041E\u0431\u0440\u0430\u0431\u043E\u0442\u043A\u0430 \u0434\u0435\u0439\u0441\u0442\u0432\u0438\u0439
        if (Request::checkCSRF()) {
            $ids = Request::post('check');
            if (!empty($ids)) {
                switch (Request::post('action')) {
                    case 'delete':
                        ContentFeedback::deleteOne($ids);
                        break;
                }
            }
        }

        // \u041E\u0442\u043E\u0431\u0440\u0430\u0436\u0435\u043D\u0438\u0435
        $filter = PaginationService::initFilter();

        $keyword = Request::get('keyword', 'string');
        if (!empty($keyword)) {
            $filter['search'] = $keyword;
            Design::assign('keyword', $keyword);
        }

        $feedbacks       = ContentFeedback::getList($filter, order: 'id');
        $feedbacks_count = ContentFeedback::getCount($filter);

        Design::assign('pagination', PaginationService::getPagination($feedbacks_count, $filter));
        Design::assign('feedbacks', $feedbacks);
        Design::assign('feedbacks_count', $feedbacks_count);

        return $this->fetchExtResponse('feedback_list.tpl');
    }
}
