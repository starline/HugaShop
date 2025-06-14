<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.2
 *
 */

namespace App\Controller\Admin\User;

use HugaShop\Api\Design;
use HugaShop\Api\Request;
use HugaShop\Api\Settings;
use HugaShop\Api\User\UserCoupon;
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

        $filter = [];
        $filter['page'] =  max(1, Request::get('page', 'int'));
        $filter['limit'] = Request::get('page', 'string') == 'all' ? 'all' : Settings::getParam('products_num_admin');

        // Поиск
        $keyword = Request::get('keyword', 'string');
        if (!empty($keyword)) {
            $filter['keyword'] = $keyword;
            Design::assign('keyword', $keyword);
        }

        $coupons_count =    UserCoupon::countCoupons($filter);
        $coupons =          UserCoupon::getCoupons($filter);

        Design::assign('pages_count', ceil($coupons_count / Settings::getParam('products_num_admin')));
        Design::assign('current_page', $filter['limit'] == 'all' ? 'all' : $filter['page']);

        Design::assign('coupons', $coupons);
        Design::assign('coupons_count', $coupons_count);

        return $this->fetchResponse('user/coupon_list.tpl');
    }
}
