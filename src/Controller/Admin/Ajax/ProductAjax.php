<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.4
 *
 */

namespace App\Controller\Admin\Ajax;

use HugaShop\Services\Request;
use App\Services\LanguageService;
use App\Controller\BaseAdminController;
use HugaShop\Models\Product\ProductOption;
use HugaShop\Models\Product\ProductFeature;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

class ProductAjax extends BaseAdminController
{
    #[Route('/admin/ajax/product/get_feature', name: 'ProductAjaxAdmin')]
    public function get_feature()
    {

        $this->checkAdminAccess(['product_content', 'product_price']);

        $category_id    = Request::getInt('category_id');
        $product_id     = Request::getInt('product_id');

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

        $this->checkAdminAccess(['product_content', 'product_price']);

        // Init content language
        LanguageService::languageCatch();

        $limit      = 100;
        $keyword    = Request::get('query', 'string');
        $feature_id = Request::getInt('feature_id');

        $options = ProductOption::getAllVariants(["feature_id" => $feature_id, "keyword" => $keyword, "limit" => $limit]);
        $options_value = $options?->pluck('value');

        $res = new \stdClass();
        $res->query = $keyword;
        $res->suggestions = $options_value;

        return new JsonResponse($res);
    }


    /**
     * Get feature name
     */
    #[Route('/admin/ajax/product/get_feature_name')]
    public function get_feature_name()
    {

        $this->checkAdminAccess(['product_content', 'product_price']);

        $limit      = 100;
        $keyword    = Request::get('query', 'string');
        $features   = ProductFeature::getFeatures(['keyword' => $keyword, 'limit' => $limit]);

        $features_value = [];
        foreach ($features as $feature) {
            $features_value[] = $feature->name;
        }

        $res = new \stdClass();
        $res->query = $keyword;
        $res->suggestions = $features_value;

        return new JsonResponse($res);
    }
}
