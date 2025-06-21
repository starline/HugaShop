<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.2
 * 
 * ProductAdmin
 *
 */

namespace App\Controller\Admin\Product;

use stdClass;
use HugaShop\Api\Image;
use HugaShop\Api\Design;
use HugaShop\Api\Request;
use HugaShop\Api\Settings;
use HugaShop\Api\SeoKeywords;
use HugaShop\Api\Product\Product;
use HugaShop\Api\User\UserPermission;
use HugaShop\Api\Product\ProductBrand;
use App\Controller\BaseAdminController;
use HugaShop\Api\Localization\Language;
use HugaShop\Api\Product\ProductOption;
use HugaShop\Api\Product\ProductFeature;
use HugaShop\Api\Product\ProductCategory;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use HugaShop\Api\Product\ProductCategoryFeature;

class ProductController extends BaseAdminController
{

    private $entity_params = [
        'id' =>                 ['type' => 'int'],
        'url' =>                ['type' => 'varchar'],
        'name' =>               ['type' => 'varchar',       'req' => true],
        'meta_title' =>         ['type' => 'varchar'],
        'meta_description' =>   ['type' => 'varchar'],
        'annotation' =>         ['type' => 'varchar'],
        'body' =>               ['type' => 'text'],
        'brand_id' =>           ['type' => 'int'],
        'category_id' =>        ['type' => 'int']
    ];


    #[Route('/admin/product', name: 'ProductNewAdmin')]
    #[Route('/admin/product/{id}', requirements: ['id' => '\d+'], name: 'ProductAdmin')]
    public function index(?int $id = null): Response
    {

        if (!UserPermission::checkAccess('product_content')) { # Check acces
            if (!empty($id)) {
                return $this->redirectToRoute('ProductId', ['id' => $id]);
            } else {
                return $this->redirectToRoute('ProductListAdmin');
            }
        }


        $languages = Language::getList();
        $main_language = $languages->firstWhere('main', 1);
        $current_lang = Request::get('lang', 'string') ?: $main_language;
        Design::assign('languages', $languages);
        Design::assign('current_language', $current_lang);


        #### Update
        ###########
        if (!empty($product = Request::getDataAcces(($this->entity_params)))) {

            if ($current_lang !== $main_language && !empty($product->id)) {
                Product::updateTranslation($product->id, $current_lang, (array) $product);
                return $this->redirectToRoute('ProductAdmin', ['id' => $product->id, 'lang' => $current_lang]);
            }

            if (empty($product->id)) {
                $product = Design::setFlashMessage('add', Product::addProduct($product));
            } else {
                Design::setFlashMessage('update', Product::updateProduct($product->id, $product));
            }

            $product = Product::getProduct($product->id);

            SeoKeywords::catchKeywords($product->id, 'product');
            Image::catchImages($product->id, 'product', 'images');
            Image::catchImages($product->id, 'product_content', 'images_content');

            // Характеристики товара
            // Удалим все из товара
            foreach (ProductOption::getProductOptions($product->id) as $po) {
                ProductOption::deleteOption($product->id, $po->feature_id);
            }

            // Характеристики текущей категории
            $category_features = array();
            foreach (ProductFeature::getFeatures(['category_id' => $product->category_id]) as $f) {
                $category_features[] = $f->id;
            }

            // Устанавливаем харакетристики товара
            if (is_array($options = Request::post('options'))) {
                foreach ($options as $f_id => $val) {

                    $option = new \stdClass();
                    $option->feature_id = $f_id;
                    $option->value = $val;

                    ProductOption::updateOption(intval($product->id), $option->feature_id, $option->value);

                    if (!in_array($option->feature_id, $category_features)) {
                        ProductCategoryFeature::addFeatureCategory($option->feature_id, $product->category_id);
                    }
                }
            }

            // Новые характеристики
            $new_features_names = Request::post('new_features_names');
            $new_features_values = Request::post('new_features_values');
            if (is_array($new_features_names) && is_array($new_features_values)) {
                foreach ($new_features_names as $i => $name) {
                    $value = trim($new_features_values[$i]);

                    if (!empty($name) && !empty($value)) {

                        $feature = ProductFeature::getFeature($name);
                        if (empty($feature)) {
                            $feature = ProductFeature::create(['name' => trim($name)]);
                        }

                        ProductCategoryFeature::addFeatureCategory($feature->id, $product->category_id);
                        ProductOption::updateOption(intval($product->id), $feature->id, $value);
                    }
                }
            }

            $params = ['id' => $product->id];
            if ($current_lang !== $main_language) {
                $params['lang'] = $current_lang;
            }
            return $this->redirectToRoute('ProductAdmin', $params);
        }


        #### View
        #########
        if (!empty($id)) {

            $product = Product::getProduct(intval($id), join: [
                'images',
                'images_content',
                'options'
            ]);

            if ($current_lang !== $main_language) {
                if ($tr = Product::getTranslation($product->id, $current_lang)) {
                    foreach (['name', 'meta_title', 'meta_description', 'annotation', 'body'] as $f) {
                        $product->$f = $tr->$f;
                    }
                } else {
                    foreach (['name', 'meta_title', 'meta_description', 'annotation', 'body'] as $f) {
                        $product->$f = null;
                    }
                }
            }

            if (empty($product->id)) {
                return $this->redirectToRoute('ProductListAdmin');
            }

            // SEO keywords
            $seo_keywords = SeoKeywords::getKeywords($product->id, 'product');

            // Все свойства товара
            if (!empty($product->category_id)) {
                $features = ProductFeature::getFeatures(['category_id' => $product->category_id]);
                Design::assign('features', $features);
            }

            Design::assign('seo_keywords', $seo_keywords);
            Design::assign('product', $product);
        }

        $brands         = ProductBrand::getBrands(); # All Products Brands
        $categories     = ProductCategory::getCategoriesTree(); #Все категории

        Design::assign('brands', $brands);
        Design::assign('categories', $categories);

        return $this->fetchResponse('product/product.tpl');
    }
}
