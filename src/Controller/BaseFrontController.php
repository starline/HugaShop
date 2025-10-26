<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 3.9
 *
 */

namespace App\Controller;

use HugaShop\Models\Settings;
use HugaShop\Services\Config;
use HugaShop\Services\Design;
use HugaShop\Services\Helper;
use HugaShop\Models\Cart\Cart;
use HugaShop\Models\User\User;
use HugaShop\Models\Localization\Language;
use HugaShop\Models\Product\ProductCategory;
use Symfony\Contracts\Service\Attribute\Required;

class BaseFrontController extends BaseController
{

    #[Required]
    public function init()
    {

        $this->setupController();

        // Setup
        Cart::catchCartSession();

        if (!empty($message_success = Helper::getSessionMessage('message_success'))) {
            Design::assign('message_success', $message_success);
        }

        Design::assign([
            'config'            => Config::getAll(), # Configuration
            'settings'          => Settings::getAllParams(),
            'user'              => User::authUser(),
            'categories'        => ProductCategory::getCategoriesTree(['visible' => 1]),
            'languages'         => Language::getLanguages(),
            'current_language'  => Language::getCurrent(),
            'main_language'     => Language::getMain()
        ]);

        // Smarty Plugins
        Design::setModifierPlugin("instock",                $this, 'checkInstock');
        Design::setModifierPlugin("api",                    $this, 'getModelMethod');
    }


    /**
     * Get Models Method
     * Use: 'ContentPage'|api:getMenu:[[var1 => value1, var2 => value2], value3]
     */
    public function getModelMethod(string $model_name, $method, array $params = [])
    {
        // If we have API Class Addons
        if (class_exists("HugaShop\\Models\\{$model_name}")) {
            $Model = "HugaShop\\Models\\{$model_name}";
        } else {
            preg_match('/^[A-Z][a-z]*/', $model_name, $matches);
            $subfolder = ucfirst($matches[0]);
            if (class_exists("HugaShop\\Models\\{$subfolder}\\{$model_name}")) {
                $Model = "HugaShop\\Models\\{$subfolder}\\{$model_name}";
            } else {
                return null;
            }
        }

        return $Model::$method(...$params);
    }


    /**
     * Show product stock
     * Smarty Modifier
     * Example: $count|instock:4:'заканчивается'
     * @param $count
     * @param $limit
     * @param string $return_txt)
     */
    public function checkInstock(int $count, int $limit, string $return_txt)
    {
        if ($count < $limit) {
            return $return_txt;
        }
        return;
    }
}
