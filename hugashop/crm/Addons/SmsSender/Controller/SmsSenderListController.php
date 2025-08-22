<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.6
 *
 */

namespace HugaShop\Addons\SmsSender\Controller;

use HugaShop\Services\Design;
use HugaShop\Services\Secure;
use HugaShop\Services\Request;
use App\Controller\BaseAdminController;
use HugaShop\Addons\BaseAddonTrait;
use Symfony\Component\Routing\Attribute\Route;
use HugaShop\Addons\SmsSender\Models\SmsSender;

final class SmsSenderListController extends BaseAdminController
{
    use BaseAddonTrait;

    /**
     * Список рассылок
     */
    #[Route('/SmsSender', name: 'AddonSmsSenderList', priority: 20)]
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
        Design::assign('addon', $this->getAddon());

        return $this->fetchAddonResponse('index.tpl');
    }
}
