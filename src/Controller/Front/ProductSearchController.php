<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.2
 *
 */

namespace App\Controller\Front;

use HugaShop\Models\Design;
use HugaShop\Models\Request;
use HugaShop\Models\Settings;
use App\Services\PaginationService;
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

        $filter = PaginationService::initFilter();
        $filter['keyword'] = $search_query;
        $filter['visible'] = 1;


        // Сортировка
        if ($sort = Request::get('sort', 'string')) {
            $filter['sort'] = $sort;
            $noindex = true; # Close indexation
        } else {
            $filter['sort'] = 'position';
        }

        $filter['sort_in_stock'] = true;
        $filter['sort_disable'] = true;


        //  Выбираем товары
        $products           = Product::getProducts($filter, ['image']);
        $products_count     = Product::countProducts($filter); # Вычисляем количество страниц

        // Закрываем пагинатор от индексации
        if (!empty(Request::get('page'))) {
            $noindex = true; # Close indexation
        }

        Design::assign('keyword', $search_query);
        Design::assign('products_count', $products_count);
        Design::assign('products', $products);
        Design::assign('pagination', PaginationService::getPagination($products_count, $filter));
        Design::assign('sort', $filter['sort']);
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
