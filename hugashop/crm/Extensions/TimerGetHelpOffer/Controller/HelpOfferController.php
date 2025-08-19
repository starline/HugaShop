<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.2
 */

namespace HugaShop\Extensions\TimerGetHelpOffer\Controller;

use HugaShop\Services\Design;
use HugaShop\Services\Helper;
use HugaShop\Services\Secure;
use HugaShop\Services\Request;
use HugaShop\Services\NotifierFactory;
use App\Controller\BaseFrontController;
use HugaShop\Extensions\BaseExtensionTrait;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use HugaShop\Extensions\TimerGetHelpOffer\Models\HelpOffer;
use HugaShop\Extensions\TimerGetHelpOffer\Services\NotifyService;
use HugaShop\Models\User\User;

final class HelpOfferController extends BaseFrontController
{
    use BaseExtensionTrait;

    #[Route('/TimerGetHelpOffer/form', name: 'ExtTimerGetHelpOfferForm', priority: 20)]
    public function form(): Response
    {

        $hasConsent = Request::post('personal_data');
        $isLogged   = User::isLoggedIn();

        if (!empty($request = Secure::getInputAcces(HelpOffer::getFields()))) {

            if ($hasConsent && ($isLogged || Helper::checkCaptcha())) {
                $request->ip         = $_SERVER['REMOTE_ADDR'];
                $request->user_agent = $_SERVER['HTTP_USER_AGENT'];
                $request             = HelpOffer::createOne($request);

                NotifierFactory::sendToManagers([
                    NotifyService::class,
                    'offerToAdmin'
                ], [
                    'request' => $request
                ]);

                return $this->fetchExtResponse('form.tpl', 'request_sent');
            } else {
                Design::assign('error', $hasConsent ? 'captcha' : 'personal_data');
            }
        }

        Design::assign('request', $request);
        Design::assign('personal_data', $hasConsent);

        return $this->fetchExtResponse('form.tpl', 'help_offer');
    }
}
