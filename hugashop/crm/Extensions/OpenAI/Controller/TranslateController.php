<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.7
 */

namespace HugaShop\Extensions\OpenAI\Controller;

use OpenAI;
use HugaShop\Services\Request;
use HugaShop\Models\Product\Product;
use App\Controller\BaseAdminController;
use HugaShop\Models\Content\ContentPage;
use HugaShop\Models\Content\ContentPost;
use HugaShop\Models\User\UserPermission;
use HugaShop\Models\Product\ProductBrand;
use HugaShop\Models\Localization\Language;
use HugaShop\Extensions\BaseExtensionTrait;
use HugaShop\Models\Product\ProductFeature;
use HugaShop\Models\Product\ProductCategory;
use Symfony\Component\Routing\Attribute\Route;
use HugaShop\Extensions\SeoPage\Models\SeoPage;
use HugaShop\Models\Product\ProductFeatureOption;
use Symfony\Component\HttpFoundation\JsonResponse;
use HugaShop\Extensions\InfoBlock\Models\InfoBlock;

final class TranslateController extends BaseAdminController
{

    use BaseExtensionTrait;

    /**
     * Translate model fields using OpenAI
     */
    #[Route('/OpenAI/ajax/translate', name: 'ExtOpenAITranslate', priority: 20)]
    public function index()
    {
        if (!Request::checkCSRF()) {
            return new JsonResponse(['error' => 'csrf']);
        }

        $entity    = Request::post('entity', 'string');
        $id        = Request::postInt('id');
        $lang_code = Request::post('lang', 'string');
        $save      = Request::post('save', 'int');

        if (empty($entity) || empty($id) || empty($lang_code)) {
            return new JsonResponse(['error' => 'params']);
        }

        $language = Language::getOne(['code' => $lang_code]);
        if (empty($language)) {
            return new JsonResponse(['error' => 'language']);
        }

        if ($language->code == Language::getMain()->code) {
            return new JsonResponse(['error' => 'is_main_language']);
        }

        $model = null;
        switch ($entity) {
            case 'product':
                UserPermission::checkAccess('product_content');
                $model = Product::query()->find($id);
                break;
            case 'category':
                UserPermission::checkAccess('category');
                $model = ProductCategory::query()->find($id);
                break;
            case 'blog':
                UserPermission::checkAccess('blog');
                $model = ContentPost::query()->find($id);
                break;
            case 'page':
                UserPermission::checkAccess('page');
                $model = ContentPage::query()->find($id);
                break;
            case 'info_block':
                $model = InfoBlock::query()->find($id);
                break;
            case 'brand':
                UserPermission::checkAccess('product_brand');
                $model = ProductBrand::query()->find($id);
                break;
            case 'feature':
                UserPermission::checkAccess('product_feature');
                $model = ProductFeature::query()->find($id);
                break;
            case 'seo_page':
                UserPermission::checkAccess('extension');
                $model = SeoPage::query()->find($id);
                break;
            default:
                return new JsonResponse(['error' => 'entity']);
        }

        if (empty($model)) {
            return new JsonResponse(['error' => 'not_found']);
        }

        $key = self::getSettings()->api_key;
        if (empty($key)) {
            return new JsonResponse(['error' => 'openai_key']);
        }

        $client = OpenAI::client($key);

        $translated = [];
        foreach ($model::getTranslatableFields() as $field) {
            if (!empty($model->$field)) {
                $result = $client->chat()->create([
                    'model' => 'gpt-4o',
                    'messages' => [
                        ['role' => 'system', 'content' => 'Ты переводчик. Переводишь на ' . $language->name . '. Всегда возвращай только переведённый текст, без комментариев.'],
                        ['role' => 'user', 'content' => $model->$field],
                    ]
                ]);
                $translated[$field] = trim($result->choices[0]->message->content);
            }
        }


        // Translate feature options
        if ($entity === 'feature') {
            $options = ProductFeatureOption::getList(['feature_id' => $model->id]);
            $translated_options = [];

            foreach ($options as $option) {
                $result = $client->chat()->create([
                    'model' => 'gpt-4o',
                    'messages' => [
                        ['role' => 'system', 'content' => 'Ты переводчик. Переводишь на ' . $language->name . '. Всегда возвращай только переведённый текст, без комментариев.'],
                        ['role' => 'user', 'content' => $option->value],
                    ]
                ]);

                $translated_options[] = ['id' => $option->id, 'value' => trim($result->choices[0]->message->content)];
            }

            if ($save && !empty($translated_options)) {
                ProductFeatureOption::updateFeatureOptions($model->id, $translated_options);
            }

            $translated['options'] = $translated_options;
        }


        if ($save && !empty($translated)) {
            $model::updateOrCreateTranslation($model->id, $language->code, $translated);
        }

        return new JsonResponse($translated);
    }
}
