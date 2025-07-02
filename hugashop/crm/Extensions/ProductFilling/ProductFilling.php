<?php

/**
 * HugaShop - Sell anything
 * 
 * @author Andri Huga
 * @version 1.2
 * 
 * Extension calculates content filling percent for products
 */

namespace HugaShop\Extensions\ProductFilling;

use HugaShop\Services\Design;
use HugaShop\Services\Request;
use App\Services\PaginationService;
use HugaShop\Extensions\BaseExtension;
use HugaShop\Models\Product\ProductCategory;
use HugaShop\Extensions\ProductFilling\Models\Product;
use HugaShop\Extensions\ProductsImport\Services\Calculate;

final class ProductFilling extends BaseExtension
{


    /**
     * Show products with filling percent
     */
    public function index()
    {

        $filter = PaginationService::initFilter();

        $category_id = Request::getInt('category_id');
        $filter['category_id'] = $category_id;
        if ($category_id && ($category = ProductCategory::getCategoryById($category_id))) {
            $filter['category_id'] = $category->children;
            Design::assign('category', $category);
        }

        if ($keyword = Request::get('keyword')) {
            $filter['keyword'] = $keyword;
            Design::assign('keyword', $keyword);
        }

        $products       = Product::getProducts($filter, join: ['image', 'fillings']);
        $products_count = Product::countProducts($filter);

        $categories = ProductCategory::getCategoriesTree();

        Design::assign('categories', $categories);
        Design::assign('products', $products);
        Design::assign('products_count', $products_count);
        Design::assign('pagination', PaginationService::getPagination($products_count, $filter));

        return $this->getTemplatePath('templates/product_list.tpl');
    }


    /**
     * Ajax
     */
    public function calculate()
    {
        if (Request::post('calculate')) {
            Calculate::calculateAllProducts();
            Request::makeRedirect('/admin/extension/ProductFilling');
        }
    }
}
