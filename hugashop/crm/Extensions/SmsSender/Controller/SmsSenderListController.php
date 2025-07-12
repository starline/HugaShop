<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.0
 *
 */

namespace HugaShop\Extensions\SmsSender\Controller;

use HugaShop\Services\Design;
use HugaShop\Services\Request;
use App\Controller\BaseAdminController;
use HugaShop\Extensions\BaseExtensionTrait;
use Symfony\Component\Routing\Attribute\Route;
use HugaShop\Extensions\SmsSender\Models\SmsSender as ModelSmsSender;

final class SmsSenderListController extends BaseAdminController
{
    use BaseExtensionTrait;

    /**
     * Список рассылок
     */
    #[Route('/SmsSender', name: 'ExtSmsSenderList', priority: 20)]
    public function index()
    {
        // Обработка действий
        if (Request::checkCSRF()) {
            $ids = Request::post('check');
            if (is_array($ids) && Request::post('action') === 'delete') {
                ModelSmsSender::deleteOne($ids);
            }
        }

        $mailings = ModelSmsSender::getList();
        Design::assign('mailings', $mailings);

        return $this->fetchExtResponse('index.tpl');
    }
}
