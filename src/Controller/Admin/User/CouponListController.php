<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.3
 *
 */

namespace App\Controller\Admin\User;

use HugaShop\Services\Design;
use HugaShop\Models\Request;
use App\Services\PaginationService;
use HugaShop\Models\User\UserCoupon;
use App\Controller\BaseAdminController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CouponListController extends BaseAdminController
{
    #[Route('/admin/user/coupons', name: 'CouponListAdmin')]
    public function index(): Response
    {

        $this->checkAdminAccess('user_coupon');

        // Обработка действий
        if (Request::checkCSRF()) {

            // Действия с выбранными
            $ids = Request::post('check');
            if (is_array($ids) && count($ids) > 0) {
                switch (Request::post('action')) {
                    case 'delete': {
                            Design::setFlashMessage('delete', UserCoupon::deleteOne($ids));
                            break;
                        }
                }
            }
        }

        $filter = PaginationService::initFilter();

        // Поиск
        $keyword = Request::get('keyword', 'string');
        if (!empty($keyword)) {
            $filter['keyword'] = $keyword;
            Design::assign('keyword', $keyword);
        }

        $coupons_count =    UserCoupon::countCoupons($filter);
        $coupons =          UserCoupon::getCoupons($filter);

        Design::assign('pagination', PaginationService::getPagination($coupons_count, $filter));

        Design::assign('coupons', $coupons);
        Design::assign('coupons_count', $coupons_count);

        return $this->fetchResponse('user/coupon_list.tpl');
    }
}
