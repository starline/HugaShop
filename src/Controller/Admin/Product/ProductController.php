<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.3
 * 
 * ProductAdmin
 *
 */

namespace App\Controller\Admin\Product;

use HugaShop\Services\Design;
use App\Services\ImageService;
use HugaShop\Services\Request;
use HugaShop\Models\SeoKeywords;
use App\Services\LanguageService;
use HugaShop\Models\Product\Product;
use App\Controller\BaseAdminController;
use HugaShop\Models\User\UserPermission;
use HugaShop\Models\Product\ProductBrand;
use HugaShop\Models\Product\ProductOption;
use HugaShop\Models\Product\ProductFeature;
use HugaShop\Models\Product\ProductCategory;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use HugaShop\Models\Product\ProductCategoryFeature;

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

        // Init content language
        LanguageService::languageCatch();

        #### Update
        ###########
        if (!empty($product = Request::getDataAcces(($this->entity_params)))) {

            if (empty($product->id)) {
                $product = Design::setFlashMessage('add', Product::addProduct($product));
            } else {
                Design::setFlashMessage('update', Product::updateProduct($product->id, $product));
            }

            SeoKeywords::catchKeywords($product->id, 'product');
            ImageService::catchImages($product->id, 'product', 'images');

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
            if ($options = Request::post('options', 'array')) {
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
            $new_features_names     = Request::post('new_features_names', 'array');
            $new_features_values    = Request::post('new_features_values', 'array');
            if (is_array($new_features_names) && is_array($new_features_values)) {
                foreach ($new_features_names as $i => $name) {
                    $value = trim($new_features_values[$i]);

                    if (!empty($name) && !empty($value)) {

                        $feature = ProductFeature::getFeature($name);
                        if (empty($feature)) {
                            $feature = ProductFeature::createOne(['name' => trim($name)]);
                        }

                        ProductCategoryFeature::addFeatureCategory($feature->id, $product->category_id);
                        ProductOption::updateOption($product->id, $feature->id, $value);
                    }
                }
            }

            return $this->redirectToRouteLang('ProductAdmin', ['id' => $product->id]);
        }


        #### View
        #########
        if (!empty($id)) {
            $product = Product::getOneEditTranslate($id, join: ['images']);

            if (empty($product->id)) {
                return $this->redirectToRoute('ProductListAdmin');
            }

            $options = ProductOption::getListTranslate(['product_id' => $product->id])->keyBy('feature_id');
            $features_ids = $options->pluck('feature_id')->all();
            $options_ids = $options->pluck('option_id')->all();

            Design::assign('options',       $options);
            Design::assign('features',      ProductFeature::getListTranslate(['id' => $features_ids]));
            Design::assign('seo_keywords',  SeoKeywords::getKeywords($product->id, 'product'));
        }

        Design::assign('product',       $product);
        Design::assign('brands',        ProductBrand::getBrands()); # All Products Brands
        Design::assign('categories',    ProductCategory::getCategoriesTree()); # Все категории

        return $this->fetchResponse('product/product.tpl');
    }
}
