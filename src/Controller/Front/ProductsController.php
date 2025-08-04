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
use HugaShop\Models\Product\ProductFeatureOption;
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

    #[Route('/{url}', name: 'Products')]
    #[Route('/{url}/{filter}', name: 'ProductsFilter')]
    public function products(string $url, ?string $filter = null): Response
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
        $product_filter = PaginationService::initFilter();
        $product_filter['visible'] = 1;

        $noindex = true; # Right away close indexation


        // If selected only category.
        if (empty(Request::gets())) {
            Design::assign('canonical', $this->generateUrlWithLocale('Products', ['url' => $category->url])); # Set hard canonical url
            Design::assign('show_description', true);
            $noindex = false; # Open indexation
        }

        $product_filter['category_id'] = $category->children;

        // Сортировка
        if ($sort = Request::get('sort', 'string')) {
            $product_filter['sort'] = $sort;
            $noindex = true; # Close indexation
        } else {
            $product_filter['sort'] = 'position';
        }

        $product_filter['sort_in_stock'] = true;
        $product_filter['sort_disable'] = true;

        // Характеристики
        $category_features = ProductFeature::getCategoryFeatures($category->id);

        // Check allowed feature from GET
        $selected_features = [];
        foreach ($category_features as $feature) {
            if (($val = strval(Request::get($feature->id))) != '' || (!empty($feature->url) and $val = strval(Request::get($feature->url))) != '') {
                if ($option_id = ProductFeatureOption::where('id', $val)->where('feature_id', $feature->id)->first()?->id) {
                    $selected_features[$feature->id] = $option_id;
                }
            }
        }

        if (!empty($selected_features)) {
            Design::assign('canonical', Request::url($selected_features, clear: true)); # Set canonical url
            $product_filter['features'] = $selected_features;
        }

        // Опции Характеристик
        $features = ProductOption::getOptions([
            'product_visible'   => 1,
            'category_id'       => $category->children,
            'feature_in_filter' => 1,
            'feature_selected'  => $selected_features
        ]);


        // Выбираем товары
        $products        = Product::getProducts($product_filter, ['image']);
        $products_count  = Product::countProducts($product_filter);


        // Закрываем пагинатор от индексации
        if (!empty(Request::get('page'))) {
            $noindex = true; # Close indexation
        }

        Design::assign('meta_title',        $category->meta_title ?: $category->name);
        Design::assign('meta_description',  $category->meta_description ?: $category->meta_title . ' ' . Settings::getParam('product_meta_description'));
        Design::assign('h1',                $category->h1 ?: $category->name);

        Design::assign('products',          $products);
        Design::assign('products_count',    $products_count);

        Design::assign('noindex',           $noindex);
        Design::assign('pagination',        PaginationService::getPagination($products_count, $product_filter));
        Design::assign('sort',              $product_filter['sort']);
        Design::assign('category',          $category);
        Design::assign('features',          $features);

        return $this->fetchResponse('products.tpl');
    }
}
