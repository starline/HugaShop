<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.7
 */

namespace HugaShop\Addons\OpenAI\Controller;

use OpenAI;
use HugaShop\Services\Secure;
use HugaShop\Services\Request;
use HugaShop\Addons\BaseAddonTrait;
use HugaShop\Models\Product\Product;
use App\Controller\BaseAdminController;
use HugaShop\Models\Content\ContentPage;
use HugaShop\Models\Content\ContentPost;
use HugaShop\Models\User\UserPermission;
use HugaShop\Addons\SeoPage\Models\SeoPage;
use HugaShop\Models\Product\ProductCategory;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

final class FillingController extends BaseAdminController
{

    use BaseAddonTrait;

    /**
     * Generate product description using OpenAI
     */
    #[Route('/OpenAI/ajax/filling', name: 'AddonOpenAIFilling', priority: 20)]
    public function index()
    {
        if (!Secure::checkCSRF()) {
            return new JsonResponse(['error' => 'csrf']);
        }

        $entity    = Request::post('entity', 'string');
        $id        = Request::postInt('id');
        $lang_code = Request::post('lang', 'string');

        if (empty($entity) || empty($id) || empty($lang_code)) {
            return new JsonResponse(['error' => 'params']);
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
            case 'seo_page':
                UserPermission::checkAccess('addon');
                $model = SeoPage::query()->find($id);
                break;
            default:
                return new JsonResponse(['error' => 'entity']);
        }

        $key = self::getSettings()->api_key;
        if (empty($key)) {
            return new JsonResponse(['error' => 'openai_key']);
        }

        // Берем название для генерации описания
        if (empty($model) || empty($model->name)) {
            return new JsonResponse(['error' => 'model']);
        }

        $client = OpenAI::client($key);

        // 1. По названию определяем основной ключевой запрос
        $result = $client->chat()->create([
            'model' => 'gpt-4o',
            'messages' => [
                ['role' => 'system', 'content' => 'Ты переводчик. Переводишь на . Всегда возвращай только переведённый текст, без комментариев.'],
                ['role' => 'user', 'content' =>  $model->name],
            ],
        ]);

        return new JsonResponse(['description' => trim($result->choices[0]->message->content ?? '')]);
    }
}
