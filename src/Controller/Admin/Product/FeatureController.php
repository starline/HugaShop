<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.3
 *
 */

namespace App\Controller\Admin\Product;

use HugaShop\Services\Design;
use HugaShop\Services\Request;
use App\Services\LanguageService;
use App\Controller\BaseAdminController;
use HugaShop\Models\Product\ProductOption;
use HugaShop\Models\Product\ProductFeature;
use HugaShop\Models\Product\ProductCategory;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use HugaShop\Models\Product\ProductFeatureVariant;
use HugaShop\Models\Product\ProductCategoryFeature;

class FeatureController extends BaseAdminController
{

    #[Route('/admin/product/feature', name: 'FeatureNewAdmin')]
    #[Route('/admin/product/feature/{id}', requirements: ['id' => '\d+'], name: 'FeatureAdmin')]
    public function index(?int $id = null): Response
    {

        $this->checkAdminAccess('product_feature');

        // Init content language
        LanguageService::languageCatch();

        $feature_categories = [];
        $feature_variants = [];
        $options = [];


        #### Update
        ###########
        if (!empty($feature = Request::getDataAcces(ProductFeature::getFields()))) {

            if (empty($feature->id)) {
                $feature = Design::setFlashMessage('add', ProductFeature::createOne($feature));
            } else {
                Design::setFlashMessage('update', ProductFeature::updateOne($feature->id, $feature));
            }

            $feature_categories = Request::post('feature_categories', 'array');
            ProductCategoryFeature::updateFeatureCategories($feature->id, $feature_categories);

            $feature_variants = Request::post('feature_variants', 'array');
            ProductFeatureVariant::updateFeatureVariants($feature->id, $feature_variants);

            // Делаем редирект на страницу с ID
            return $this->redirectToRouteLang('FeatureAdmin', ['id' => $feature->id]);
        }


        #### View
        #########
        if (!empty($id)) {
            $feature = ProductFeature::getOneEditTranslate($id);

            if (empty($feature->id)) {
                return $this->redirectToRoute('FeatureListAdmin');
            }

            $feature_categories = ProductCategoryFeature::getFeatureCategories($feature->id);
            $feature_variants   = ProductFeatureVariant::getFeatureVariants($feature->id);

            // Используемые значения характеристики
            $options = ProductOption::getAllVariants($feature->id);
        }

        $categories = ProductCategory::getCategoriesTree();

        Design::assign('feature', $feature);
        Design::assign('options', $options);
        Design::assign('categories', $categories);
        Design::assign('feature_categories', $feature_categories);
        Design::assign('feature_variants', $feature_variants);

        return $this->fetchResponse('product/feature.tpl');
    }
}
