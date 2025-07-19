<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.5
 *
 */

namespace App\Controller\Front;

use App\Event\SearchEvent;
use App\Event\ProductSearchEvent;
use HugaShop\Models\Image;
use HugaShop\Models\Settings;
use HugaShop\Services\Design;
use HugaShop\Services\Request;
use App\Services\PaginationService;
use HugaShop\Models\Product\Product;
use App\Controller\BaseFrontController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

class ProductSearchController extends BaseFrontController
{

    #[Route('/s/{search_query}', name: 'ProductsSearch', priority: 10)]
    public function search(?string $search_query = null): Response
    {

        //$search_query = trim(str_replace('+', ' ',  $search_query)); # use + for whitespace
        $search_query = trim(substr(urldecode($_SERVER['REQUEST_URI']), 3));
        $this->setEvent(new ProductSearchEvent($search_query));

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


    /**
     * Ajax product search
     */
    #[Route('/ajax/product/search', name: 'AjaxProductSearch', priority: 1)]
    public function search_ajax(): JsonResponse
    {

        // Поиск (без 'string' - потому-что сжирает запятые)
        $query = Request::get('query', 'string');
        if (!empty($query)) {
            $filter['keyword'] = $query;
            $this->setEvent(new ProductSearchEvent($query));
            $this->setEvent(new SearchEvent($query));
        }

        $filter['limit'] = Settings::getParam('products_num');
        $filter['visible'] = 1;

        $products = Product::getProducts($filter, join: ['image']);
        $suggestions = [];

        foreach ($products as $product) {

            $product_sug = new \stdClass();
            $product_sug->name = $product->name;
            $product_sug->id = $product->id;
            if (!empty($product->image_filename)) {
                $product_sug->image = Image::getImageURL($product->image_filename, 60, 60, 'c');
            }

            $suggestion = new \stdClass();
            $suggestion->value = $product->name;
            $suggestion->data = $product_sug;

            $suggestions[] = $suggestion;
        }

        if (count($suggestions) == 0) {
            $suggestion = new \stdClass();
            $suggestion->value = 'По вышему запрос не найдено товаров';
            $suggestions[] = $suggestion;
        }

        $res = new \stdClass();
        $res->query = $query;
        $res->suggestions = $suggestions;

        return new JsonResponse($res);
    }
}
