<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.3
 */

namespace App\Controller\Admin\Ajax;

use HugaShop\Services\Extension;
use App\Controller\BaseAdminController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

class TranslateAjax extends BaseAdminController
{
    #[Route('/admin/ajax/translate', name: 'TranslateAjaxAdmin')]
    public function translate()
    {
        $Ext = Extension::makeExtension('OpenAI');

        if (empty($Ext)) {
            return new JsonResponse(['error' => 'extension'], 500);
        }

        return new JsonResponse($Ext->translate());
    }
}
