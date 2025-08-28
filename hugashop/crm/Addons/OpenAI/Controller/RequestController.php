<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.0
 *
 * OpenAI custom request playground
 */

namespace HugaShop\Addons\OpenAI\Controller;

use OpenAI;
use HugaShop\Services\Secure;
use HugaShop\Services\Request;
use App\Controller\BaseAdminController;
use HugaShop\Addons\BaseAddonTrait;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

final class RequestController extends BaseAdminController
{
    use BaseAddonTrait;

    /**
     * Show request playground page
     */
    #[Route('/OpenAI', name: 'AddonOpenAI', priority: 20)]
    public function index()
    {
        return $this->fetchAddonResponse('request.tpl');
    }

    /**
     * Send custom request to OpenAI
     */
    #[Route('/OpenAI/ajax/request', name: 'AddonOpenAIRequest', priority: 20)]
    public function request()
    {
        if (!Secure::checkCSRF()) {
            return new JsonResponse(['error' => 'csrf']);
        }

        $system_content = Request::post('system_content', 'string');
        $user_content   = Request::post('user_content', 'string');

        if (empty($system_content) || empty($user_content)) {
            return new JsonResponse(['error' => 'params']);
        }

        $key = self::getSettings()->api_key;
        if (empty($key)) {
            return new JsonResponse(['error' => 'openai_key']);
        }

        $client = OpenAI::client($key);
        $result = $client->chat()->create([
            'model' => 'gpt-4o',
            'messages' => [
                ['role' => 'system', 'content' => $system_content],
                ['role' => 'user', 'content' => $user_content],
            ],
        ]);

        return new JsonResponse([
            'content' => trim($result->choices[0]->message->content),
        ]);
    }
}
