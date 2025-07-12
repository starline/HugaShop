<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.6
 */

namespace HugaShop\Extensions\OpenAI\Controller;

use OpenAI;
use HugaShop\Services\Request;
use HugaShop\Models\Product\Product;
use App\Controller\BaseAdminController;
use HugaShop\Extensions\BaseExtensionTrait;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

final class FillingController extends BaseAdminController
{

    use BaseExtensionTrait;

    /**
     * Generate product description using OpenAI
     */
    #[Route('/OpenAI/ajax/filling', name: 'ExtOpenAIFilling', priority: 20)]
    public function index()
    {
        if (!Request::checkCSRF()) {
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
