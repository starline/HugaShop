<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.1
 *
 */

namespace App\Controller\Front;

use HugaShop\Models\Design;
use HugaShop\Models\Request;
use HugaShop\Models\Settings;
use HugaShop\Models\Product\Product;
use App\Controller\BaseFrontController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProductSearchController extends BaseFrontController
{

    #[Route('/s/{search_query}', name: 'ProductsSearch', priority: 10)]
    public function search(?string $search_query = null): Response
    {

        //$search_query = trim(str_replace('+', ' ',  $search_query)); # use + for whitespace
        $search_query = trim(substr(urldecode($_SERVER['REQUEST_URI']), 3));

        $noindex = false; # Open indexation
        $products_filter['keyword'] = $search_query;


        // Сортировка
        if ($sort = Request::get('sort', 'string')) {
            $products_filter['sort'] = $sort;
            $noindex = true; # Close indexation
        } else {
            $products_filter['sort'] = 'position';
        }

        $products_filter['sort_in_stock'] = true;
        $products_filter['sort_disable'] = true;


        // Постраничная навигация
        $items_per_page = Settings::getParam('products_num');
        $current_page = Request::get('page', 'int');   # Текущая страница в постраничном выводе
        $current_page = max(1, $current_page);                    # Если не задана, то равна 1

        $products_count = Product::countProducts($products_filter); # Вычисляем количество страниц


        // Закрываем пагинатор от индексации
        if (!empty(Request::get('page'))) {
            $noindex = true; # Close indexation
        }

        $products_filter['page'] = $current_page;
        $products_filter['limit'] = $items_per_page;


        //  Выбираем товары
        $products_sku = [];
        $products = Product::getProducts($products_filter, ['image']);

        Design::assign('keyword', $search_query);

        Design::assign('pages_count', ceil($products_count / $items_per_page));
        Design::assign('total_products_num', $products_count);
        Design::assign('current_page', $current_page);

        Design::assign('products', $products);
        Design::assign('sort', $products_filter['sort']);
        Design::assign('noindex', $noindex);
        Design::assign('meta_title', $search_query);
        Design::assign('meta_description', $search_query . ' ' . Settings::getParam('product_meta_description'));


        return $this->fetchResponse('product_search.tpl');
    }


    /**
     * Old search url
     * make redirect
     */
    #[Route('/search/{search_query}', name: 'ProductsOldSearch')]
    public function search_old(string $search_query)
    {
        return $this->redirectToRoute('ProductsSearch', ['search_query' => $search_query], 301);
    }
}
