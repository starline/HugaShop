<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.1
 *
 * OpenAI custom request playground
 * 
 */

namespace HugaShop\Addons\OpenAI\Controller;

use HugaShop\Services\Design;
use HugaShop\Services\Secure;
use HugaShop\Services\Request;
use HugaShop\Addons\BaseAddonTrait;
use App\Controller\BaseAdminController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use HugaShop\Addons\OpenAI\Services\OpenAIServices;

final class RequestController extends BaseAdminController
{
    use BaseAddonTrait;

    /**
     * Show request playground page
     */
    #[Route('/OpenAI', name: 'AddonOpenAI', priority: 20)]
    public function index()
    {
        Design::assign('addon', $this->getAddon());
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

        if (empty(self::getSettings()?->api_key)) {
            return new JsonResponse(['error' => 'openai_key']);
        }

        $result = OpenAIServices::chatCreate($system_content, $user_content, 'gpt-4o');

        return new JsonResponse([
            'content' => trim($result->choices[0]->message->content)
        ]);
    }
}
