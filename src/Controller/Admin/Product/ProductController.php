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
use HugaShop\Api\SeoKeywords;
use HugaShop\Api\Product\Product;
use HugaShop\Api\User\UserPermission;
use HugaShop\Api\Product\ProductBrand;
use App\Controller\BaseAdminController;
use HugaShop\Api\Product\ProductOption;
use HugaShop\Api\Product\ProductFeature;
use HugaShop\Api\Product\ProductCategory;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use HugaShop\Api\Product\ProductFeatureVariant;
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


        #### Update
        ###########
        if (!empty($product = Request::getDataAcces(($this->entity_params)))) {

            if (empty($product->id)) {
                $product->id = Design::setFlashMessage('add', Product::addProduct($product));
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

            return $this->redirectToRoute('ProductAdmin', ['id' => $product->id]);
        }


        #### View
        #########
        if (!empty($id)) {

            $product = Product::getProduct(intval($id));

            if (empty($product->id)) {
                return $this->redirectToRoute('ProductListAdmin');
            }

            // SEO keywords
            $seo_keywords = SeoKeywords::getKeywords($product->id, 'product');

            // Изображения товара
            $images = Image::getImages($product->id, 'product');
            $images_content = Image::getImages($product->id, 'product_content');

            // Свойства товара
            $options = ProductOption::getProductOptions($product->id);
            if (is_array($options)) {
                $temp_options = [];
                foreach ($options as $option) {
                    $temp_options[$option->feature_id] = $option;
                }
                $options = $temp_options;
            }

            Design::assign('seo_keywords', $seo_keywords);
            Design::assign('product_images', $images);
            Design::assign('images_content', $images_content);
            Design::assign('options', $options);
        }


        #### Create View
        #########
        else {

            $product  = new stdClass();

            if ($category_id = Request::get('category_id')) {
                $product->category_id = $category_id;
            }

            if ($brand_id = Request::get('brand_id')) {
                $product->brand_id = $brand_id;
            }
        }

        $brands = ProductBrand::getBrands(); # All Products Brands
        $categories = ProductCategory::getCategoriesTree(); #Все категории

        // Все свойства товара
        if (!empty($product->category_id)) {
            $features = ProductFeature::getFeatures(['category_id' => $product->category_id]);
            Design::assign('features', $features);

            if (!empty($features)) {
                foreach ($features as &$feature) {
                    $feature->variants = ProductFeatureVariant::getFeatureVariants($feature->id);
                }
            }
        }

        Design::assign('product', $product);
        Design::assign('brands', $brands);
        Design::assign('categories', $categories);

        return $this->fetchResponse('product/product.tpl');
    }
}
