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
use HugaShop\Models\Product\Product;
use App\Controller\BaseAdminController;
use HugaShop\Addons\BaseAddonTrait;
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

        $id = Request::postInt('id');
        if (empty($id)) {
            return new JsonResponse(['error' => 'params']);
        }

        $product = Product::query()->find($id);
        if (empty($product)) {
            return new JsonResponse(['error' => 'not_found']);
        }

        $key = self::getSettings()->api_key;
        if (empty($key)) {
            return new JsonResponse(['error' => 'openai_key']);
        }

        $client = OpenAI::client($key);

        $result = $client->chat()->create([
            'model' => 'gpt-4o',
            'messages' => [
                ['role' => 'user', 'content' => 'сделай описание для карточки товара'],
            ],
        ]);

        return new JsonResponse(['description' => trim($result->choices[0]->message->content ?? '')]);
    }
}
