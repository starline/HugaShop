<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.1
 *
 * OpenAI integration
 */

namespace HugaShop\Extensions\OpenAI;

use OpenAI as AI;
use HugaShop\Services\Request;
use HugaShop\Models\Product\Product;
use HugaShop\Extensions\BaseExtension;
use HugaShop\Models\Content\ContentPage;
use HugaShop\Models\Content\ContentPost;
use HugaShop\Models\User\UserPermission;
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
        $save      = Request::post('save', 'int');

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

        $product = null;
        switch ($entity) {
            case 'product':
                UserPermission::checkAccess('product_content');
                $product = Product::query()->find($id);
                break;
            case 'blog':
                UserPermission::checkAccess('blog');
                $product = ContentPost::query()->find($id);
                break;
            case 'page':
                UserPermission::checkAccess('page');
                $product = ContentPage::query()->find($id);
                break;
            case 'brand':
                UserPermission::checkAccess('product_brand');
                $product = ProductBrand::query()->find($id);
                break;
            default:
                return ['error' => 'entity'];
        }

        if (empty($product)) {
            return ['error' => 'not_found'];
        }

        $key = $this->getSetting('api_key');
        if (empty($key)) {
            return ['error' => 'openai_key'];
        }

        $client = AI::client($key);

        $translated = [];
        foreach ($product::getTranslatableFields() as $field) {
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

        if ($save && !empty($translated)) {
            $product::updateTranslation($product->id, $language->code, $translated);
        }

        return $translated;
    }



    /**
     * Generate product description using OpenAI
     */
    public function filling()
    {
        if (!Request::checkCSRF()) {
            return ['error' => 'csrf'];
        }

        $id = Request::postInt('id');
        if (empty($id)) {
            return ['error' => 'params'];
        }

        $product = Product::query()->find($id);
        if (empty($product)) {
            return ['error' => 'not_found'];
        }

        $key = $this->getSetting('api_key');
        if (empty($key)) {
            return ['error' => 'openai_key'];
        }

        $client = AI::client($key);

        $result = $client->chat()->create([
            'model' => 'gpt-4o',
            'messages' => [
                ['role' => 'user', 'content' => 'сделай описание для карточки товара'],
            ],
        ]);

        return [
            'description' => trim($result->choices[0]->message->content ?? ''),
        ];
    }
}
