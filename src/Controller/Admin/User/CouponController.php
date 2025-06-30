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
use HugaShop\Models\Helper;
use HugaShop\Services\Request;
use HugaShop\Models\User\UserCoupon;
use App\Controller\BaseAdminController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CouponController extends BaseAdminController
{
    #[Route('/admin/user/coupon', name: 'CouponNewAdmin')]
    #[Route('/admin/user/coupon/{id}', requirements: ['id' => '\d+'], name: 'CouponAdmin')]
    public function index(?int $id = null): Response
    {

        $this->checkAdminAccess('user_coupon');

        #### Update
        ###########
        if (!empty($coupon = Request::getDataAcces(UserCoupon::getFields()))) {

            $expires = Request::post('expires', 'bool');
            if (!empty($expires) and !empty($coupon->expire)) {
                $coupon->expire = Helper::dateConvert($coupon->expire . ' 12:00', 'Y-m-d');
            } else {
                unset($coupon->expire);
            }

            // Не допустить одинаковые КОДЫ купонов.
            if (($temp_c = UserCoupon::getCoupon((string)$coupon->code)) && $temp_c->id != $coupon->id) {
                Design::setFlashMessage('message_error', 'code_exists');
            } else {

                if (empty($coupon->id)) {
                    $coupon = Design::setFlashMessage('add', UserCoupon::createOne($coupon));
                } else {
                    Design::setFlashMessage('update', UserCoupon::updateOne($coupon->id, $coupon));
                }
            }

            // Делаем редирект на страницу с ID
            return $this->redirectToRoute('CouponAdmin', ['id' => $coupon->id]);
        }


        #### View
        #########
        if (!empty($id)) {
            $coupon = UserCoupon::getCoupon($id);

            if (empty($coupon->id)) {
                return $this->redirectToRoute('CouponListAdmin');
            }
        }


        //if(empty($coupon->id))
        //$coupon->expire = date(Settings::getParam('date_format), time());

        Design::assign('coupon', $coupon);

        return $this->fetchResponse('user/coupon.tpl');
    }
}
