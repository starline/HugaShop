<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 3.2
 *
 */

namespace App\Controller;

use HugaShop\Api\Cart\Cart;
use HugaShop\Api\User\User;
use HugaShop\Api\Config;
use HugaShop\Api\Design;
use HugaShop\Api\Helper;
use HugaShop\Api\Product\Product;
use HugaShop\Api\Request;
use HugaShop\Api\Settings;
use HugaShop\Api\Product\ProductVariant;
use HugaShop\Api\Finance\FinanceCurrency;
use HugaShop\Api\Product\ProductCategory;
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
        Design::setModifierPlugin("api",                    $this, 'getApiMethod');
    }


    /**
     * Get Api Method
     * Use: 'ContentPage'|api:getMenu:[[var1 => value1, var2 => value2], value3]
     */
    public function getApiMethod(string $api_class, $method, array $params = [])
    {
        // If we have API Class Extensions
        if (class_exists("HugaShop\\Api\\{$api_class}")) {
            $ClassName = "HugaShop\\Api\\{$api_class}";
        } else {
            preg_match('/^[A-Z][a-z]*/', $api_class, $matches);
            $subfolder = ucfirst($matches[0]);
            if (class_exists("HugaShop\\Api\\{$subfolder}\\{$api_class}")) {
                $ClassName = "HugaShop\\Api\\{$subfolder}\\{$api_class}";
            } else {
                return null;
            }
        }

        return $ClassName::$method(...$params);
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

            $browsed_products = Product::getProducts(['id' => $browsed_products_ids, 'visible' => 1], ['image']);

            if (!empty($browsed_products)) {

                // id выбраных товаров
                $pids = array_keys($browsed_products);

                // Выбираем варианты товаров
                $variants = ProductVariant::getVariants(['product_id' => $pids]);

                // Для каждого варианта, добавляем вариант в соответствующий товар
                foreach ($variants as &$variant) {
                    $browsed_products[$variant->product_id]->variants[] = $variant;
                }

                foreach ($browsed_products as &$product) {
                    if (isset($product->variants[0])) {
                        $product->variant = $product->variants[0];
                    }
                }

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
