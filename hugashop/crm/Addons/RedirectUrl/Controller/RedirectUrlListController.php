<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.9
 *
 */

namespace HugaShop\Addons\RedirectUrl\Controller;

use HugaShop\Services\Design;
use HugaShop\Services\Secure;
use HugaShop\Services\Request;
use App\Controller\BaseAdminController;
use HugaShop\Addons\BaseAddonTrait;
use Symfony\Component\Routing\Attribute\Route;
use HugaShop\Addons\RedirectUrl\Models\RedirectUrl;

final class RedirectUrlListController extends BaseAdminController
{
    use BaseAddonTrait;

    /**
     * Url list
     */
    #[Route('/RedirectUrl', name: 'AddonRedirectUrlList', priority: 20)]
    public function links()
    {
        // Handle actions
        if (Secure::checkCSRF()) {
            $ids = Request::post('check');
            if (is_array($ids)) {
                switch (Request::post('action')) {
                    case 'disable':
                        RedirectUrl::updateList($ids, ['enabled' => 0]);
                        break;
                    case 'enable':
                        RedirectUrl::updateList($ids, ['enabled' => 1]);
                        break;
                    case 'delete':
                        foreach ($ids as $id) {
                            RedirectUrl::deleteOne($id);
                        }
                        break;
                }
            }
            RedirectUrl::cacheClear();
        }

        Design::assign('links',     RedirectUrl::getList());
        Design::assign('addon', $this->getAddon());

        return $this->fetchAddonResponse('link_list.tpl');
    }
}
