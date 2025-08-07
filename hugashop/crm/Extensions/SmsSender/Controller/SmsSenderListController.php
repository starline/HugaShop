<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.5
 *
 */

namespace HugaShop\Extensions\SmsSender\Controller;

use HugaShop\Services\Design;
use HugaShop\Services\Secure;
use HugaShop\Services\Request;
use App\Controller\BaseAdminController;
use HugaShop\Extensions\BaseExtensionTrait;
use Symfony\Component\Routing\Attribute\Route;
use HugaShop\Extensions\SmsSender\Models\SmsSender;

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
        if (Secure::checkCSRF()) {
            $ids = Request::post('check');
            if (is_array($ids) && Request::post('action') === 'delete') {
                SmsSender::deleteOne($ids);
            }
        }

        Design::assign('mailings', SmsSender::getList());
        Design::assign('extension', $this->getExtension());

        return $this->fetchExtResponse('index.tpl');
    }
}
