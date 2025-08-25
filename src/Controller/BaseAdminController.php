<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 3.5
 *
 */

namespace App\Controller;

use HugaShop\Models\Settings;
use HugaShop\Services\Config;
use HugaShop\Services\Design;
use HugaShop\Services\Secure;
use HugaShop\Models\User\User;
use HugaShop\Services\Request;
use HugaShop\Models\Order\Order;
use HugaShop\Services\Addon;
use HugaShop\Models\User\UserPermission;
use HugaShop\Models\Localization\Language;
use HugaShop\Models\Content\ContentComment;
use HugaShop\Models\Finance\FinanceCurrency;
use Symfony\Contracts\Service\Attribute\Required;

class BaseAdminController extends BaseController
{

    #[Required]
    public function initBaseAdmin()
    {

        // Check User Auth
        if (empty(User::authUser('manager'))) {
            Request::setCurrentPage();
            Request::makeRedirect($this->UrlGenerator->generate('UserLogin'));
        }

        // Setup
        $admin_theme = 'admin';
        Design::initSettings(['theme' => $admin_theme, 'packages' => $this->Packages]);

        $current_language = Language::getCurrent();
        $this->setTranslator($current_language->code, $admin_theme);

        // Order INFO count for top menu
        // 0 - new order
        // 1 - accepted order
        // 4 - shipped order
        foreach ([0, 1, 4] as $status) {
            $orders_info_count[$status] = Order::getOrdersCount(filter: ['status' => $status]);
        }

        Design::assign([
            'config'                => Config::getAll(), # Configuration
            'settings'              => Settings::getAllParams(),
            'user'                  => User::authUser(),
            'currency'              => FinanceCurrency::getMainCurrency(),
            'orders_info_count'     => $orders_info_count,
            'new_comments_counter'  => ContentComment::getCommentsCount(filter: ['approved' => 0]),
            'addons_menu'           => Addon::getMenuAddons(),
            'languages'             => Language::getLanguages(),
            'current_language'      => $current_language,
            'main_language'         => Language::getMain()
        ]);
    }


    /**
     * Check admin access
     * @param string $access_type
     * Example: 'Order' | ['Order', 'User']
     */
    public function checkAdminAccess(string|array $access_type, bool $checkCSRF = false)
    {
        if ($checkCSRF and !Secure::checkCSRF()) {
            throw $this->createNotFoundException('Access denied...'); # 404
        }

        if (!UserPermission::checkAccess($access_type)) { # Check acces
            throw $this->createNotFoundException('Access denied'); # 404
        }
        return true;
    }


    /**
     * Redirect to route with translation 
     */
    public function redirectToRouteLang(string $rout_name, array $params = [])
    {

        if ($language_code = Language::checkOrGetCode()) {
            $params['lang'] = $language_code;
        }
        return $this->redirectToRoute($rout_name, $params);
    }
}
