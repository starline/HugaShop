<?php

/**
 * HugaShop - Sell anything
 *
 * Extension calculates content filling percent for products
 */

namespace HugaShop\Extensions\Productsfilling;

use HugaShop\Models\Design;
use HugaShop\Models\Helper;
use HugaShop\Models\Request;
use HugaShop\Models\Settings;
use HugaShop\Models\Localization\Language;
use HugaShop\Models\Product\Product;
use HugaShop\Models\Product\ProductCategory;
use HugaShop\Extensions\BaseExtension;
use HugaShop\Extensions\Productsfilling\Models\Productsfilling as Model;

final class Productsfilling extends BaseExtension
{
    /**
     * Show products with filling percent
     */
    public function index()
    {
        if (Request::post('calculate')) {
            $this->calculateAll();
            Helper::cache(self::class)->clear();
            Request::makeRedirect('/admin/extension/Productsfilling');
        }

        $filter = [];
        $filter['page'] = max(1, Request::get('page', 'int'));
        $filter['limit'] = Request::get('page', 'string') == 'all' ? 'all' : Settings::getParam('products_num_admin');

        $category_id = Request::get('category_id', 'int');
        $filter['category_id'] = $category_id;
        if ($category_id && ($category = ProductCategory::getCategoryById($category_id))) {
            $filter['category_id'] = $category->children;
            Design::assign('category', $category);
        }

        if ($keyword = Request::get('keyword')) {
            $filter['keyword'] = $keyword;
            Design::assign('keyword', $keyword);
        }

        $products = Product::getProducts($filter, join: ['image']);
        $products_count = Product::countProducts($filter);

        foreach ($products as $product) {
            $product->filling = (int) Model::where('product_id', $product->id)->avg('percent');
        }

        $categories = ProductCategory::getCategoriesTree();

        Design::assign('categories', $categories);
        Design::assign('products', $products);
        Design::assign('products_count', $products_count);
        Design::assign('pages_count', ceil($products_count / Settings::getParam('products_num_admin')));
        Design::assign('current_page', $filter['limit'] == 'all' ? 'all' : $filter['page']);

        return $this->getTemplatePath('templates/product_list.tpl');
    }

    /**
     * Calculate filling for one product
     */
    public function calculateProduct(int $product_id)
    {
        $product = Product::getProduct($product_id);
        if (!$product) {
            return;
        }

        $fields = ['name', 'meta_title', 'meta_description', 'annotation', 'body'];
        $langs = Language::getLanguages();

        foreach ($langs as $lang) {
            $filled = 0;
            if ($lang->code == Language::$main_language_code) {
                foreach ($fields as $field) {
                    if (!empty(trim($product->$field))) {
                        $filled++;
                    }
                }
            } else {
                $translation = Product::getTranslation($product_id, $lang->code);
                foreach ($fields as $field) {
                    $val = $translation->$field ?? null;
                    if (!empty(trim($val))) {
                        $filled++;
                    }
                }
            }
            $percent = intval($filled / count($fields) * 100);
            Model::updateOrCreate([
                'product_id' => $product_id,
                'language_code' => $lang->code
            ], [
                'percent' => $percent
            ]);
        }
    }

    /**
     * Recalculate filling for all products
     */
    public function calculateAll()
    {
        $ids = Product::getList(select: 'id');
        foreach ($ids as $id) {
            $this->calculateProduct($id);
        }
    }
}
