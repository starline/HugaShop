<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.2
 */

namespace App\Controller\Admin\Ajax;

use OpenAI;
use HugaShop\Services\Config;
use HugaShop\Services\Request;
use App\Services\LanguageService;
use HugaShop\Models\Product\Product;
use App\Controller\BaseAdminController;
use HugaShop\Models\Content\ContentPage;
use HugaShop\Models\Content\ContentPost;
use HugaShop\Models\Product\ProductBrand;
use HugaShop\Models\Localization\Language;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

class TranslateAjax extends BaseAdminController
{
    #[Route('/admin/ajax/translate', name: 'TranslateAjaxAdmin')]
    public function translate()
    {
        if (!Request::checkCSRF()) {
            return new JsonResponse(['error' => 'csrf'], 400);
        }

        $entity    = Request::post('entity', 'string');
        $id        = Request::postInt('id');
        $lang_code = Request::post('lang', 'string');

        if (empty($entity) || empty($id) || empty($lang_code)) {
            return new JsonResponse(['error' => 'params'], 400);
        }

        $language = Language::getOne(['code' => $lang_code]);

        if (empty($language)) {
            return new JsonResponse(['error' => 'language'], 400);
        }

        if ($language->code == Language::getMain()->code) {
            return new JsonResponse(['error' => 'is_main_language'], 400);
        }

        $model = null;
        switch ($entity) {
            case 'product':
                $this->checkAdminAccess('product_content');
                $model = Product::query()->find($id);
                break;
            case 'blog':
                $this->checkAdminAccess('blog');
                $model = ContentPost::query()->find($id);
                break;
            case 'page':
                $this->checkAdminAccess('page');
                $model = ContentPage::query()->find($id);
                break;
            case 'brand':
                $this->checkAdminAccess('product_brand');
                $model = ProductBrand::query()->find($id);
                break;
            default:
                return new JsonResponse(['error' => 'entity'], 400);
        }

        if (empty($model)) {
            return new JsonResponse(['error' => 'not_found'], 404);
        }

        $key = Config::get('openai')->key;
        if (empty($key)) {
            return new JsonResponse(['error' => 'openai_key'], 500);
        }

        $client = OpenAI::client($key);

        $translated = [];
        foreach ($model::getTranslatableFields() as $field) {
            if (!empty($model->$field)) {
                $result = $client->chat()->create([
                    'model' => 'gpt-4o',
                    'messages' => [
                        ['role' => 'user', 'content' => 'Переведи на ' . $language->name . ': ' . $model->$field],
                    ],
                ]);
                $translated[$field] = trim($result->choices[0]->message->content);
            }
        }

        return new JsonResponse($translated);
    }
}
