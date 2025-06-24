<?php

/**
 *
 * @author Andi Huga
 * @version 1.9
 *
 */

namespace App\Controller\Front\Exchange;

use HugaShop\Models\Extension;
use App\Controller\BaseFrontController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ExtensionController extends BaseFrontController
{
    #[Route('/ext/{name}/{token}', priority: 2, name: 'ExtensionTokenExchange')]
    #[Route('/ext/{name}/{id}/{token}', requirements: ['id' => '\d+'], priority: 3, name: 'ExtensionIdTokenExchange')]
    public function index(string $name, string $token, ?int $id = null): Response
    {

        if (empty($name) || empty($token) || empty($extension = Extension::makeExtension($name))) {
            throw $this->createNotFoundException('Something is going wrong.'); # 404
        }

        if (!method_exists($extension, 'webhook')) {
            throw $this->createNotFoundException('Access denied'); # 404
        }

        $params['id'] = $id;
        $params['token'] = $token;

        // Extension return HttpFoundation\Response
        if (!empty($response = $extension->webhook($params))) {
            return $response;
        }

        throw $this->createNotFoundException('Something is going wrong.'); # 404
    }
}
