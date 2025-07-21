<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.0
 */

namespace App\Controller\Admin\Ajax;

use HugaShop\Services\Request;
use App\Services\LockEditService;
use App\Controller\BaseAdminController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

class LockEditAjax extends BaseAdminController
{

    // Locking
    #[Route('/admin/ajax/lock/{locked_key}', name: 'LockEditAjax')]
    public function lock(string $locked_key): JsonResponse
    {
        if (!Request::checkCSRF()) {
            return new JsonResponse(['error' => 'csrf']);
        }

        $status = LockEditService::lock($locked_key) ? 'locked' : 'busy';
        return new JsonResponse(['status' => $status]);
    }

    
    // Unlocking
    #[Route('/admin/ajax/unlock/{locked_key}', name: 'UnlockEditAjax')]
    public function unlock(string $locked_key): JsonResponse
    {
        if (!Request::checkCSRF()) {
            return new JsonResponse(['error' => 'csrf']);
        }

        LockEditService::unlock($locked_key);
        return new JsonResponse(['status' => 'unlocked']);
    }
}
