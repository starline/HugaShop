<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 4.0
 *
 * Этот класс использует шаблон products.tpl
 * Отображение списка товаров, каталог товаров
 *
 */

namespace App\Controller\Front;

use HugaShop\Services\Design;
use HugaShop\Services\Request;
use HugaShop\Models\Settings;
use App\Services\PaginationService;
use HugaShop\Models\Product\Product;
use App\Controller\BaseFrontController;
use HugaShop\Models\Product\ProductOption;
use HugaShop\Models\Product\ProductFeature;
use HugaShop\Models\Product\ProductCategory;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProductsController extends BaseFrontController
{

    #[Route('/c/{id}', requirements: ['id' => '\d+'], name: 'ProductCategoryId')]
    public function category(int $id): Response
    {
        if (empty($category = ProductCategory::getCategoryById($id))) {
            throw $this->createNotFoundException('Product does not found'); # 404
        }
        return $this->redirectToRoute('Products', ['url' => $category->url], 301);
    }


    #[Route('/{url}/c{id}', requirements: ['id' => '\d+'], name: 'ProductCategory')]
    #[Route('/{url}', name: 'Products')]
    public function products(string $url, ?int $id = null): Response
    {

        // Выберем текущую категорию
        $category = ProductCategory::getCategoryByURL($url);
        if (empty($category)) {
            throw $this->createNotFoundException('Category does not found'); # 404
        }

        // Redirect to canonical page
        if ($category->url !== $url) {
            return $this->redirectToRoute('ProductCategory', ['url' => $category->url], 301);
        }


        // Постраничная навигация
        $filter = PaginationService::initFilter();
        $filter['visible'] = 1;

        $noindex = true; # Right away close indexation


        // If selected only category.
        if (empty(Request::gets())) {
            Design::assign('canonical', $this->generateUrlWithLocale('Products', ['url' => $category->url])); # Set hard canonical url
            Design::assign('show_description', true);
            $noindex = false; # Open indexation
        }

        $filter['category_id'] = $category->children;

        // Сортировка
        if ($sort = Request::get('sort', 'string')) {
            $filter['sort'] = $sort;
            $noindex = true; # Close indexation
        } else {
            $filter['sort'] = 'position';
        }

        $filter['sort_in_stock'] = true;
        $filter['sort_disable'] = true;


        // Характеристики
        $features = [];
        $selected_features = [];
        foreach (ProductFeature::getFeatures(['category_id' => $category->id, 'in_filter' => 1]) as $feature) {
            if (($val = strval(Request::get($feature->id))) != '') {
                $selected_features[$feature->id] = $val;
            }
        }

        // Характеристики
        $options_filter['visible'] = 1;
        $options_filter['category_id'] = $category->children;
        if (!empty($features)) {
            $options_filter['feature_id'] = array_keys($features);
        }

        if (!empty($selected_features)) {
            Design::assign('canonical', Request::url($selected_features, true)); # Set canonical, clear other params
            $options_filter['features'] = $selected_features;
            $filter['features'] = $selected_features;
        }

        /*$options = ProductOption::getOptions($options_filter);
        foreach ($options as $option) {
            if (isset($features[$option->feature_id])) {
                $features[$option->feature_id]->options[] = $option;
            }
        }*/

        // Delete fetures withot options
        foreach ($features as $i => &$feature) {
            if (empty($feature->options)) {
                unset($features[$i]);
            }
        }

        // Выбираем товары
        $products        = Product::getProducts($filter, ['image']);
        $products_count  = Product::countProducts($filter);


        // Закрываем пагинатор от индексации
        if (!empty(Request::get('page'))) {
            $noindex = true; # Close indexation
        }

        if (empty($category->meta_title)) {
            $category->meta_title = $category->name;
        }

        // Если description пустой, берем title + product_meta_description
        if (empty($category->meta_description)) {
            $category->meta_description = $category->meta_title . ' ' . Settings::getParam('product_meta_description');
        }


        Design::assign('meta_title',        $category->meta_title);
        Design::assign('meta_description',  $category->meta_description);

        Design::assign('products',          $products);
        Design::assign('products_count',    $products_count);

        Design::assign('noindex',           $noindex);
        Design::assign('pagination',        PaginationService::getPagination($products_count, $filter));
        Design::assign('sort',              $filter['sort']);
        Design::assign('category',          $category);
        Design::assign('features',          $features);

        return $this->fetchResponse('products.tpl');
    }
}
