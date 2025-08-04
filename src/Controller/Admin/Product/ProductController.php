<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.5
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
use HugaShop\Models\Product\ProductFeatureOption;
use HugaShop\Models\Product\ProductCategoryFeature;

class ProductController extends BaseAdminController
{

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
        if (!empty($product = $this->updateOrCreateHeandle($id))) {
            return $this->redirectToRouteLang('ProductAdmin', ['id' => $product->id]);
        }


        #### View
        #########
        if (!empty($id)) {
            $product = Product::getOneEditTranslate($id, join: ['images']);
            if (empty($product->id)) {
                return $this->redirectToRoute('ProductListAdmin');
            }

            $features_options               = ProductOption::getList(['product_id' => $product->id]);
            $features_ids                   = $features_options->pluck('feature_id')->all();
            $options_ids                    = $features_options->pluck('option_id')->all();

            Design::assign('options',       ProductFeatureOption::getListTranslate(['id' => $options_ids])->keyBy('feature_id'));
            Design::assign('features',      ProductFeature::getListTranslate(['id' => $features_ids], 'position'));
            Design::assign('seo_keywords',  SeoKeywords::getKeywords($product->id, 'product'));
            Design::assign('product',       $product);
        }

        Design::assign('brands',        ProductBrand::getBrands()); # All Products Brands
        Design::assign('categories',    ProductCategory::getCategoriesTree()); # Ctegories tree

        return $this->fetchResponse('product/product.tpl');
    }


    /**
     * Update or Create heandle
     */
    private function updateOrCreateHeandle($id)
    {

        if (empty($product = Request::getInputCheckEditAccess(Product::class, $id))) {
            return;
        }

        if (empty($product->id)) {
            $product = Design::setFlashMessage('add', Product::createOne($product));
        } else {
            Design::setFlashMessage('update', Product::updateProduct($product->id, $product));
        }

        SeoKeywords::catchKeywords($product->id, 'product');
        ImageService::catchImages($product->id, 'product');

        // Сначала очищаем связи, чтобы удалить неактуальные данные
        // TODO удалить все не переданные. после обновления и добавления
        ProductOption::query()->where('product_id', $product->id)->delete();

        // Характеристики текущей категории
        $category_features = [];
        foreach (ProductFeature::getFeatures(['category_id' => $product->category_id]) as $f) {
            $category_features[] = $f->id;
        }

        // Устанавливаем характеристики товара
        if ($options = Request::post('options', 'array')) {
            foreach ($options as $feature_id => $option_data) {

                $option_id  = array_key_first($option_data);
                $value      = trim((string) $option_data[$option_id]);

                if ($value === '') {
                    continue;
                }

                if (!empty($option_id)) {
                    $feature_option = ProductFeatureOption::find($option_id);
                    if ($feature_option) {
                        $feature_option->value = $value;
                        $feature_option->save();
                    } else {
                        $feature_option = ProductFeatureOption::createOne([
                            'feature_id' => $feature_id,
                            'value'      => $value,
                        ]);
                    }
                } else {
                    $feature_option = ProductFeatureOption::firstOrCreate([
                        'feature_id' => $feature_id,
                        'value'      => $value,
                    ]);
                }

                ProductOption::updateOrCreate(
                    ['product_id' => $product->id, 'feature_id' => $feature_id],
                    ['option_id' => $feature_option->id]
                );

                if (!in_array($feature_id, $category_features)) {
                    ProductCategoryFeature::addFeatureCategory($feature_id, $product->category_id);
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

                    $feature = ProductFeature::getFeatureByName($name);
                    if (empty($feature)) {
                        $feature = ProductFeature::createOne(['name' => trim($name)]);
                    }

                    ProductCategoryFeature::addFeatureCategory($feature->id, $product->category_id);
                    $feature_option = ProductFeatureOption::firstOrCreate([
                        'feature_id' => $feature->id,
                        'value'      => $value,
                    ]);

                    ProductOption::updateOrCreate(
                        ['product_id' => $product->id, 'feature_id' => $feature->id],
                        ['option_id' => $feature_option->id]
                    );
                }
            }
        }

        return $product;
    }
}
