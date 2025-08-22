<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.4
 */

namespace HugaShop\Addons\Feedback\Controller;

use HugaShop\Services\Design;
use HugaShop\Services\Secure;
use HugaShop\Services\Request;
use App\Services\PaginationService;
use App\Controller\BaseAdminController;
use HugaShop\Addons\BaseAddonTrait;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use HugaShop\Addons\Feedback\Models\Feedback;

final class FeedbackListController extends BaseAdminController
{
    use BaseAddonTrait;

    #[Route('/Feedback', name: 'ExtFeedbackList', priority: 20)]
    public function index(): Response
    {

        if (Secure::checkCSRF()) {
            $ids = Request::post('check');
            if (!empty($ids)) {
                switch (Request::post('action')) {
                    case 'delete':
                        Feedback::deleteOne($ids);
                        break;
                }
            }
        }

        $filter = PaginationService::initFilter();

        $keyword = Request::get('keyword', 'string');
        if (!empty($keyword)) {
            $filter['search'] = $keyword;
            Design::assign('keyword', $keyword);
        }

        $feedbacks       = Feedback::getList($filter, order: ['id', 'desc']);
        $feedbacks_count = Feedback::getCount($filter);

        Design::assign('pagination', PaginationService::getPagination($feedbacks_count, $filter));
        Design::assign('feedbacks', $feedbacks);
        Design::assign('feedbacks_count', $feedbacks_count);
        Design::assign('addon', $this->getAddon());

        return $this->fetchAddonResponse('feedback_list.tpl');
    }
}
