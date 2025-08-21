<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.1
 */

namespace HugaShop\Extensions\SubscribeOffer\Controller;

use HugaShop\Services\Design;
use HugaShop\Models\User\User;
use HugaShop\Services\Request;
use App\Controller\BaseFrontController;
use HugaShop\Extensions\BaseExtensionTrait;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use HugaShop\Extensions\SubscribeOffer\Models\SubscribeOffer;
use stdClass;

final class SubscribeOfferController extends BaseFrontController
{
    use BaseExtensionTrait;

    #[Route('/SubscribeOffer/form', name: 'ExtSubscribeOfferForm', priority: 20)]
    public function form(): Response
    {
        $id    = Request::post('id', 'int');
        $email = Request::post('email', 'string');
        $error = null;

        if ($email && $id) {
            if (!User::checkEmailExists($email) && !SubscribeOffer::getOne(['email' => $email])) {
                $coupon = $this->getExtension()->settings->coupon_code;
                SubscribeOffer::updateOne($id, ['email' => $email, 'coupon_code' => $coupon]);
                Request::setSession('coupon_code', $coupon);
                Design::assign('coupon', $coupon);

                return $this->fetchExtResponse('form.tpl', 'request_sent');
            } else {
                $error = 'email_exists';
            }
        } elseif (!empty($page = Request::post('page', 'string'))) {
            $request             = new stdClass();
            $request->ip         = $_SERVER['REMOTE_ADDR'];
            $request->user_agent = $_SERVER['HTTP_USER_AGENT'];
            $request->page       = $page;
            $id                  = SubscribeOffer::createOne($request)?->id;
        }

        Design::assign('id', $id);
        Design::assign('email', $email);
        Design::assign('error', $error);

        return $this->fetchExtResponse('form.tpl', 'subscribe_offer');
    }
}
