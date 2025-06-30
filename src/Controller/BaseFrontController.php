<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 3.3
 *
 */

namespace App\Controller;

use HugaShop\Models\Cart\Cart;
use HugaShop\Models\User\User;
use HugaShop\Models\Config;
use HugaShop\Services\Design;
use HugaShop\Models\Helper;
use HugaShop\Models\Product\Product;
use HugaShop\Services\Request;
use HugaShop\Models\Settings;
use HugaShop\Models\Finance\FinanceCurrency;
use HugaShop\Models\Product\ProductCategory;
use Symfony\Contracts\Service\Attribute\Required;

class BaseFrontController extends BaseController
{

    #[Required]
    public function init()
    {

        // Setup
        Design::initSettings(['theme' => Settings::getParam('theme'), 'packages' => $this->Packages]);
        $this->setTranslator('ru', Settings::getParam('theme'));

        Cart::catchCartSession();

        if (!empty($message_success = Helper::getSessionMessage('message_success'))) {
            Design::assign('message_success', $message_success);
        }

        Design::assign([
            'config' =>     Config::get(), # Configuration
            'settings' =>   Settings::getAllParams(),
            'user' =>       User::authUser(),
            'currency' =>   FinanceCurrency::getMainCurrency(),
            'currencies' => FinanceCurrency::getCurrencies(['enabled' => 1]), # All enabled currencies
            'categories' => ProductCategory::getCategoriesTree(['visible' => 1]),
            'cart' =>       Cart::getCurrentCart() # current cart
        ]);

        // Smarty Plugins
        Design::setFunctionPlugin("get_browsed_products",   $this, 'getBrowsedProducts');
        Design::setModifierPlugin("instock",                $this, 'checkInstock');
        Design::setModifierPlugin("api",                    $this, 'getModelMethod');
    }


    /**
     * Get Models Method
     * Use: 'ContentPage'|api:getMenu:[[var1 => value1, var2 => value2], value3]
     */
    public function getModelMethod(string $model_name, $method, array $params = [])
    {
        // If we have API Class Extensions
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


    /**
     * Выбираем просмотренные продукты
     * Smarty Plugin
     */
    public function getBrowsedProducts($params, $smarty)
    {

        if (!empty($cookie_bp = Request::getCookie('BP'))) {
            $browsed_products_ids = explode('.', $cookie_bp);
            $browsed_products_ids = array_reverse($browsed_products_ids);

            if (isset($params['limit'])) {
                $browsed_products_ids = array_slice($browsed_products_ids, 0, $params['limit']);
            }

            $browsed_products = Product::getProducts(['id' => $browsed_products_ids, 'visible' => 1], join: ['image']);

            if (!empty($browsed_products)) {

                // Сортируем товары в порядке просмотра
                $browsed_products_sort = [];
                foreach ($browsed_products_ids as $bp_id) {
                    if ($browsed_products[$bp_id]) {
                        $browsed_products_sort[] = $browsed_products[$bp_id];
                    }
                }

                $smarty->assign($params['var'], $browsed_products_sort);
            }
        }
    }
}
