<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.1
 *
 */

namespace App\Controller\Admin\Ajax;

use OpenAI;
use HugaShop\Models\Config;
use HugaShop\Models\Request;
use HugaShop\Models\Product\Product;
use App\Controller\BaseAdminController;
use HugaShop\Models\Localization\Language;
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

        $category_id = Request::get('category_id', 'integer');
        $product_id = Request::get('product_id', 'integer');

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


    #[Route('/admin/ajax/product/get_option')]
    public function get_option()
    {

        $this->checkAdminAccess(['product_content', 'product_price']);

        $limit = 100;
        $keyword = Request::get('query', 'string');
        $feature_id = Request::get('feature_id', 'integer');

        $options = ProductOption::getOptions(array("feature_id" => $feature_id, "keyword" => $keyword, "limit" => $limit));

        $options_value = [];
        foreach ($options as $op) {
            $options_value[] = $op->value;
        }

        $res = new \stdClass();
        $res->query = $keyword;
        $res->suggestions = $options_value;

        return new JsonResponse($res);
    }


    #[Route('/admin/ajax/product/get_feature_name')]
    public function get_feature_name()
    {

        $this->checkAdminAccess(['product_content', 'product_price']);

        $limit = 100;
        $keyword = Request::get('query', 'string');
        $features = ProductFeature::getFeatures(['keyword' => $keyword, 'limit' => $limit]);

        $features_value = [];
        foreach ($features as $feature) {
            $features_value[] = $feature->name;
        }

        $res = new \stdClass();
        $res->query = $keyword;
        $res->suggestions = $features_value;

        return new JsonResponse($res);
    }


    #[Route('/admin/ajax/product/translate')]
    public function translate()
    {

        $this->checkAdminAccess('product_content');

        if (!Request::checkCSRF()) {
            return new JsonResponse(['error' => 'csrf'], 400);
        }

        $product_id     = Request::post('product_id', 'integer');
        $lang_code      = Request::post('lang', 'string');

        if (empty($product_id) || empty($lang_code)) {
            return new JsonResponse(['error' => 'params'], 400);
        }

        Language::languageCatch();

        if ($lang_code == Language::$main_language->code) {
            return new JsonResponse(['error' => 'main_language'], 400);
        }

        $language = Language::where('code', $lang_code)->first();
        if (empty($language)) {
            return new JsonResponse(['error' => 'language'], 400);
        }

        $product = Product::query()->find($product_id);
        if (empty($product)) {
            return new JsonResponse(['error' => 'product'], 404);
        }

        $key = Config::get('openai')->key;
        if (empty($key)) {
            return new JsonResponse(['error' => 'openai_key'], 500);
        }

        $client = OpenAI::client($key);

        $translated = [];
        foreach (Product::getTranslatableFields() as $field) {
            if (!empty($product->$field)) {
                $result = $client->chat()->create([
                    'model' => 'gpt-4o',
                    'messages' => [
                        ['role' => 'user', 'content' => 'Переведи на ' . $language->name . ': ' . $product->$field],
                    ],
                ]);
                $translated[$field] = trim($result->choices[0]->message->content);
            }
        }

        return new JsonResponse($translated);
    }
}
