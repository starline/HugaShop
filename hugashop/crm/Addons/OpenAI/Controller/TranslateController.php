<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.9
 */

namespace HugaShop\Addons\OpenAI\Controller;

use HugaShop\Services\Secure;
use HugaShop\Services\Request;
use HugaShop\Models\Product\Product;
use App\Controller\BaseAdminController;
use HugaShop\Models\Content\ContentPage;
use HugaShop\Models\Content\ContentPost;
use HugaShop\Models\User\UserPermission;
use HugaShop\Models\Product\ProductBrand;
use HugaShop\Models\Localization\Language;
use HugaShop\Addons\BaseAddonTrait;
use HugaShop\Models\Product\ProductFeature;
use HugaShop\Models\Product\ProductCategory;
use Symfony\Component\Routing\Attribute\Route;
use HugaShop\Addons\SeoPage\Models\SeoPage;
use HugaShop\Models\Product\ProductFeatureOption;
use Symfony\Component\HttpFoundation\JsonResponse;
use HugaShop\Addons\InfoBlock\Models\InfoBlock;
use HugaShop\Addons\OpenAI\Services\OpenAIService;

final class TranslateController extends BaseAdminController
{

    use BaseAddonTrait;

    /**
     * Translate model fields using OpenAI
     */
    #[Route('/OpenAI/ajax/translate', name: 'AddonOpenAITranslate', priority: 20)]
    public function index()
    {
        if (!Secure::checkCSRF()) {
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
                UserPermission::checkAccess('addon');
                $model = SeoPage::query()->find($id);
                break;
            default:
                return new JsonResponse(['error' => 'entity']);
        }

        if (empty($model)) {
            return new JsonResponse(['error' => 'not_found']);
        }

        if (empty(self::getSettings()->api_key)) {
            return new JsonResponse(['error' => 'openai_key']);
        }

        $system_content = 'You are a translator. Translate to ' . $language->name . '. Always returns only the translated text, without comments.';

        $translated = [];
        foreach ($model::getTranslatableFields() as $field) {
            if (!empty($model->$field)) {
                $result = OpenAIService::chatCreate($system_content, $model->$field, 'gpt-4o');
                $translated[$field] = trim($result->choices[0]->message->content);
            }
        }


        // Translate feature options
        if ($entity === 'feature') {
            $options = ProductFeatureOption::getList(['feature_id' => $model->id]);
            $translated_options = [];

            foreach ($options as $option) {
                $result = OpenAIService::chatCreate($system_content, $option->value, 'gpt-4o');
                $translated_options[] = [
                    'id' => $option->id,
                    'value' => trim($result->choices[0]->message->content)
                ];
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
