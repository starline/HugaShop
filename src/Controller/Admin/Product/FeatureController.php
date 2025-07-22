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
use HugaShop\Services\Request;
use App\Services\LanguageService;
use App\Controller\BaseAdminController;
use HugaShop\Models\Product\ProductFeature;
use HugaShop\Models\Product\ProductCategory;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use HugaShop\Models\Product\ProductFeatureOption;
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

        #### Update
        ###########
        if (!empty($feature = Request::getInputCheckEditAccess(ProductFeature::class, $id))) {

            if (empty($feature->id)) {
                $feature = Design::setFlashMessage('add', ProductFeature::createOne($feature));
            } else {
                Design::setFlashMessage('update', ProductFeature::updateOne($feature->id, $feature));
            }

            $feature_categories = Request::post('feature_categories', 'array');
            ProductCategoryFeature::updateFeatureCategories($feature->id, $feature_categories);

            $options = Request::post('options', 'array');
            ProductFeatureOption::updateFeatureOptions($feature->id, $options);

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

            Design::assign('feature_categories', ProductCategoryFeature::getFeatureCategories($feature->id));

            // Значения характеристики
            Design::assign('options', ProductFeatureOption::getListEditTranslate(['feature_id' => $feature->id]));
        }

        Design::assign('feature', $feature);
        Design::assign('categories', ProductCategory::getCategoriesTree());

        return $this->fetchResponse('product/feature.tpl');
    }
}
