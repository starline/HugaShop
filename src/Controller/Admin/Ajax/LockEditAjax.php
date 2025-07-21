<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.0
 */

namespace App\Controller\Admin\Ajax;

use HugaShop\Models\User\User;
use App\Services\LockEditService;
use App\Controller\BaseAdminController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

class LockEditAjax extends BaseAdminController
{

    // Locking
    #[Route('/admin/ajax/lock/{id}', name: 'LockEditAjax')]
    public function lock(int $id): JsonResponse
    {
        $this->checkAdminAccess('order', checkCSRF: true);
        $status = LockEditService::lock('order', $id, User::authUser('id')) ? 'locked' : 'busy';
        return new JsonResponse(['status' => $status]);
    }

    // Unlocking
    #[Route('/admin/ajax/unlock/{id}', name: 'UnlockEditAjax')]
    public function unlock(int $id): JsonResponse
    {
        $this->checkAdminAccess('order', checkCSRF: true);
        LockEditService::unlock('order', $id, User::authUser('id'));
        return new JsonResponse(['status' => 'unlocked']);
    }
}
