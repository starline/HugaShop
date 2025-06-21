<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 3.8
 *
 * Этот класс использует шаблон products.tpl
 * Отображение списка товаров, каталог товаров
 *
 */

namespace App\Controller\Front;

use HugaShop\Models\Design;
use HugaShop\Models\Request;
use HugaShop\Models\Settings;
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


        $products_filter = [];
        $products_filter['visible'] = 1;
        $noindex = true; # Right away close indexation


        // If selected only category.
        if (empty(Request::gets())) {
            Design::assign('canonical', $this->generateUrl('Products', ['url' => $category->url])); # Set hard canonical url
            Design::assign('show_description', true);
            $noindex = false; # Open indexation
        }

        $products_filter['category_id'] = $category->children;

        // Сортировка
        if ($sort = Request::get('sort', 'string')) {
            $products_filter['sort'] = $sort;
            $noindex = true; # Close indexation
        } else {
            $products_filter['sort'] = 'position';
        }

        $products_filter['sort_in_stock'] = true;
        $products_filter['sort_disable'] = true;


        // Характеристики
        $features = [];
        $selected_features = [];
        foreach (ProductFeature::getFeatures(['category_id' => $category->id, 'in_filter' => 1]) as $feature) {
            if (($val = strval(Request::get($feature->id))) != '') {
                $selected_features[$feature->id] = $val;
            }
        }

        // Свойства характеристик
        $options_filter['visible'] = 1;
        $options_filter['category_id'] = $category->children;
        if (!empty($features)) {
            $options_filter['feature_id'] = array_keys($features);
        }

        if (!empty($selected_features)) {
            Design::assign('canonical', Request::url($selected_features, true)); // Set canonical, clear other params
            $options_filter['features'] = $selected_features;
            $products_filter['features'] = $selected_features;
        }

        $options = ProductOption::getOptions($options_filter);
        foreach ($options as $option) {
            if (isset($features[$option->feature_id])) {
                $features[$option->feature_id]->options[] = $option;
            }
        }

        // Delete fetures withot options
        foreach ($features as $i => &$feature) {
            if (empty($feature->options)) {
                unset($features[$i]);
            }
        }


        // Постраничная навигация
        $items_per_page     = Settings::getParam('products_num');
        $current_page       = Request::get('page', 'int');   # Текущая страница в постраничном выводе
        $current_page       = max(1, $current_page);                    # Если не задана, то равна 1
        $products_count     = Product::countProducts($products_filter); # Вычисляем количество страниц


        // Закрываем пагинатор от индексации
        if (!empty(Request::get('page'))) {
            $noindex = true; # Close indexation
        }

        $products_filter['page'] = $current_page;
        $products_filter['limit'] = $items_per_page;

        //  Выбираем товары
        $products = Product::getProducts($products_filter, ['image']);

        if (empty($category->meta_title)) {
            $category->meta_title = $category->name;
        }

        // Если description пустой, берем title + product_meta_description
        if (empty($category->meta_description)) {
            $category->meta_description =  $category->meta_title . ' ' . Settings::getParam('product_meta_description');
        }


        Design::assign('meta_title', $category->meta_title);
        Design::assign('meta_description', $category->meta_description);

        Design::assign('current_page', $current_page);
        Design::assign('pages_count', ceil($products_count / $items_per_page));
        Design::assign('total_products_num', $products_count);

        Design::assign('noindex', $noindex);
        Design::assign('sort', $products_filter['sort']);
        Design::assign('category', $category);
        Design::assign('products', $products);
        Design::assign('features', $features);

        return $this->fetchResponse('products.tpl');
    }
}
