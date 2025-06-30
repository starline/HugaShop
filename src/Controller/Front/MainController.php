<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.5
 *
 * Домашняя страница сайта
 *
 */

namespace App\Controller\Front;

use HugaShop\Services\Design;
use HugaShop\Models\Settings;
use HugaShop\Models\Product\Product;
use App\Controller\BaseFrontController;
use HugaShop\Models\Product\ProductCategory;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MainController extends BaseFrontController
{

    #[Route('/', name: 'Main', priority: 8)]
    //#[Route('{_locale}', name: 'MainLocale', requirements: ['_locale' => 'ru|uk'], priority: -1)]
    public function main(): Response
    {

        // Выбираем популярные товары с основных категорий
        $categories_products = [];
        foreach (ProductCategory::getCategoriesTree() as $cat) {
            if ($cat->visible) {

                $products_filter['visible'] = 1;
                $products_filter['featured'] = 1;
                $products_filter['category_id'] = $cat->children;
                $products_filter['limit'] = 8;

                $category_products = Product::getProducts($products_filter, ['image']);

                if (!empty($category_products)) {
                    $current_category = new \stdClass();
                    $current_category->category = $cat;
                    $current_category->products = $category_products;
                    $categories_products[] = $current_category;
                }
            }
        }

        // Устанавливаем meta-теги
        Design::assign('meta_title', Settings::getParam('company_name') . ' - ' . Settings::getParam('company_description'));
        Design::assign('meta_description', Settings::getParam('company_name') . ' - ' . Settings::getParam('company_description'));
        Design::assign('canonical', $this->generateUrl('Main'));
        Design::assign('categories_products', $categories_products);

        return $this->fetchResponse('main.tpl');
    }
}
