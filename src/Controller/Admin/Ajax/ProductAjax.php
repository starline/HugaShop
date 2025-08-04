<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.5
 *
 */

namespace App\Controller\Admin\Ajax;

use HugaShop\Services\Request;
use App\Services\LanguageService;
use App\Controller\BaseAdminController;
use HugaShop\Models\Product\ProductOption;
use HugaShop\Models\Product\ProductFeature;
use Symfony\Component\Routing\Attribute\Route;
use HugaShop\Models\Product\ProductFeatureOption;
use Symfony\Component\HttpFoundation\JsonResponse;

class ProductAjax extends BaseAdminController
{

    #[Route('/admin/ajax/product/get_feature', name: 'ProductAjaxAdmin')]
    public function get_feature()
    {

        $this->checkAdminAccess(['product_content', 'product_price'], checkCSRF: true);

        $category_id    = Request::input('category_id', 'int');
        $product_id     = Request::input('product_id', 'int');

        if (!empty($category_id)) {
            $features = ProductFeature::getFeatures(['category_id' => $category_id]);
        } else {
            $features = ProductFeature::getFeatures();
        }

        $options = [];
        if (!empty($product_id)) {
            $opts = ProductOption::getProductOptions($product_id);
            foreach ($opts as $opt) {
                $options[$opt->feature_id] = $opt;
            }
        }

        foreach ($features as &$f) {
            if (isset($options[$f->id])) {
                $f->value = $options[$f->id]->value;
            } else {
                $f->value = '';
            }
        }

        return new JsonResponse($features);
    }


    /**
     * Get feature variants
     */
    #[Route('/admin/ajax/product/get_option')]
    public function get_option()
    {

        $this->checkAdminAccess(['product_content', 'product_price'], checkCSRF: true);

        // Init content language
        LanguageService::languageCatch();

        $limit          = 80;
        $keyword        = Request::input('query', 'string');
        $feature_id     = Request::input('feature_id', 'int');

        $options        = ProductFeatureOption::getListTranslate(['feature_id' => $feature_id, 'search' => $keyword, 'limit' => $limit]);

        $suggestions = [];
        foreach ($options as $option) {
            $suggestions[] = [
                'value' => $option->value,
                'id' => $option->id,
                'feature_id' => $option->feature_id
            ];
        }

        $res = new \stdClass();
        $res->query = $keyword;
        $res->suggestions = $suggestions;

        return new JsonResponse($res);
    }


    /**
     * Get feature name
     */
    #[Route('/admin/ajax/product/get_feature_name')]
    public function get_feature_name()
    {

        $this->checkAdminAccess(['product_content', 'product_price'], checkCSRF: true);

        // Init content language
        LanguageService::languageCatch();

        $limit          = 80;
        $keyword        = Request::input('query', 'string');

        $features       = ProductFeature::getListTranslate(['search' => $keyword, 'limit' => $limit]);

        $suggestions = [];
        foreach ($features as $feature) {
            $suggestions[] = ['value' => $feature->name, 'data' => $feature->id];
        }

        $res = new \stdClass();
        $res->query = $keyword;
        $res->suggestions = $suggestions;

        return new JsonResponse($res);
    }
}
