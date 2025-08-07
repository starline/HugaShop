<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 4.1
 *
 * Этот класс использует шаблон products.tpl
 * Отображение списка товаров, каталог товаров
 *
 */

namespace App\Controller\Front;

use HugaShop\Models\Settings;
use HugaShop\Services\Design;
use HugaShop\Services\Request;
use App\Services\PaginationService;
use HugaShop\Models\Product\Product;
use App\Controller\BaseFrontController;
use HugaShop\Models\Product\ProductOption;
use HugaShop\Models\Product\ProductFeature;
use HugaShop\Models\Product\ProductCategory;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use HugaShop\Models\Product\ProductFeatureOption;

class ProductsController extends BaseFrontController
{

    #[Route('/c/{id}', requirements: ['id' => '\d+'], name: 'ProductCategoryId', priority: 10)]
    public function category(int $id): Response
    {
        if (empty($category = ProductCategory::getCategoryById($id))) {
            throw $this->createNotFoundException('Product does not found'); # 404
        }
        return $this->redirectToRoute('Products', ['url' => $category->url], 301);
    }


    #[Route('/{url}', name: 'Products', priority: 1)]
    #[Route('/{url}/{filter_path}', requirements: ['filter_path' => '.+'], name: 'ProductsFilter', priority: 1)]
    public function products(string $url, ?string $filter_path = null): Response
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

        // Характеристики
        $category_features = ProductFeature::getCategoryFeatures($category->id, in_filter: true);

        // Parse options from friendly URL /filter/option1/option2
        $selected_features = [];
        $filter_urls = [];
        if (!empty($filter_path)) {
            $filter_urls = array_filter(explode('/', $filter_path));
            if (!empty($filter_urls)) {
                $options = ProductFeatureOption::query()
                    ->whereIn('url', $filter_urls)
                    ->get();

                foreach ($options as $option) {
                    if (isset($category_features[$option->feature_id])) {
                        $selected_features[$option->feature_id] = $option->id;
                    }
                }
            }
        }

        $product_filter                     = PaginationService::initFilter();
        $product_filter['visible']          = 1;
        $product_filter['category_id']      = $category->children;
        $product_filter['sort']             = Request::get('sort', 'string') ?: 'position';
        $product_filter['sort_in_stock']    = true;
        $product_filter['sort_disable']     = true;
        $product_filter['features']         = $selected_features;

        // Опции Характеристик
        $features = ProductOption::getOptions([
            'product_visible'   => 1,
            'category_id'       => $category->id,
            'feature_in_filter' => 1,
            'feature_selected'  => $selected_features
        ]);

        // Выбираем товары
        $products        = Product::getProducts($product_filter, ['image']);
        $products_count  = Product::countProducts($product_filter);

        Design::assign('meta_title',        $category->meta_title ?: $category->name);
        Design::assign('meta_description',  $category->meta_description ?: $category->meta_title . ' ' . Settings::getParam('product_meta_description'));
        Design::assign('h1',                $category->h1 ?: $category->name);

        Design::assign('products',          $products);
        Design::assign('products_count',    $products_count);

        Design::assign('pagination',        PaginationService::getPagination($products_count, $product_filter));
        Design::assign('sort',              $product_filter['sort']);
        Design::assign('category',          $category);
        Design::assign('features',          $features);
        Design::assign('selected_features', $selected_features);


        // Set canonical url
        Design::assign('canonical', $this->filterUrl(['option_url' => reset($filter_urls)]));

        $noindex = true; # Right away close indexation

        // Exclude page|sort|...
        if (empty(Request::gets())) {

            // If selected only category.
            if (empty($selected_features)) {
                Design::assign('show_description', true);
                $noindex = false; # Open indexation
            }

            // Open indexation if only one feature selected and it is indexable
            if (count($selected_features) === 1) {
                $feature_id = array_key_first($selected_features);
                if (isset($category_features[$feature_id]) && (int) $category_features[$feature_id]->index === 1) {
                    $noindex = false; # Open indexation
                }
            }
        }

        Design::assign('noindex', $noindex);
        Design::setFunctionPlugin('filter_url', $this, 'filterUrl');

        return $this->fetchResponse('products.tpl');
    }


    /**
     * Generate URL for feature option filter
     * @param array $params
     */
    public function filterUrl(array $params): string
    {
        $featureId = $params['feature_id'] ?? null;
        $optionUrl = $params['option_url'] ?? null;

        $category = Design::getTemplateVars('category');
        $selected = Design::getTemplateVars('selected_features') ?? [];
        $features = Design::getTemplateVars('features') ?? [];

        $optionUrls = [];
        foreach ($features as $fid => $feature) {
            if (isset($selected[$fid])) {
                foreach ($feature->options as $opt) {
                    if ($opt->id == $selected[$fid]) {
                        $optionUrls[$fid] = $opt->url;
                        break;
                    }
                }
            }
        }

        if ($featureId) {
            if (empty($optionUrl)) {
                unset($optionUrls[$featureId]);
            } else {
                $optionUrls[$featureId] = $optionUrl;
            }

            $sorted = [];
            foreach ($features as $fid => $feature) {
                if (isset($optionUrls[$fid])) {
                    $sorted[$fid] = $optionUrls[$fid];
                }
            }
            $optionUrls = $sorted;
        }

        $urlParams = ['url' => $category->url];
        if (!empty($optionUrls)) {
            $urlParams['filter_path'] = implode('/', $optionUrls);
            $route = 'ProductsFilter';
        } else {
            $route = 'Products';
        }

        $url = $this->generateUrlWithLocale($route, $urlParams);

        $query = Request::gets();
        unset($query['sort'], $query['page']);
        if (!empty($query)) {
            $url .= '?' . http_build_query($query);
        }

        return $url;
    }
}
