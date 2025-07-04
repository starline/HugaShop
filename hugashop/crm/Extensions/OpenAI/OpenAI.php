<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.0
 *
 * OpenAI integration
 */

namespace HugaShop\Extensions\OpenAI;

use OpenAI;
use HugaShop\Services\Config;
use HugaShop\Services\Request;
use HugaShop\Extensions\BaseExtension;
use HugaShop\Models\Product\Product;
use HugaShop\Models\Content\ContentPage;
use HugaShop\Models\Content\ContentPost;
use HugaShop\Models\Product\ProductBrand;
use HugaShop\Models\Localization\Language;

final class OpenAI extends BaseExtension
{
    /**
     * Translate model fields using OpenAI
     */
    public function translate()
    {
        if (!Request::checkCSRF()) {
            return ['error' => 'csrf'];
        }

        $entity    = Request::post('entity', 'string');
        $id        = Request::postInt('id');
        $lang_code = Request::post('lang', 'string');

        if (empty($entity) || empty($id) || empty($lang_code)) {
            return ['error' => 'params'];
        }

        $language = Language::getOne(['code' => $lang_code]);
        if (empty($language)) {
            return ['error' => 'language'];
        }

        if ($language->code == Language::getMain()->code) {
            return ['error' => 'is_main_language'];
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
                return ['error' => 'entity'];
        }

        if (empty($model)) {
            return ['error' => 'not_found'];
        }

        $key = $this->getSetting('api_key') ?? Config::get('openai')->key;
        if (empty($key)) {
            return ['error' => 'openai_key'];
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

        return $translated;
    }
}
