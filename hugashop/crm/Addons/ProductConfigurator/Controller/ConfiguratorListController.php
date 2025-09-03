<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.0
 */

namespace HugaShop\Addons\ProductConfigurator\Controller;

use HugaShop\Services\Design;
use HugaShop\Services\Secure;
use HugaShop\Services\Request;
use App\Controller\BaseAdminController;
use HugaShop\Addons\BaseAddonTrait;
use Symfony\Component\Routing\Attribute\Route;
use HugaShop\Addons\ProductConfigurator\Models\Configurator;

final class ConfiguratorListController extends BaseAdminController
{
    use BaseAddonTrait;

    #[Route('/ProductConfigurator', name: 'AddonProductConfiguratorList', priority: 20)]
    public function index()
    {
        if (Secure::checkCSRF()) {
            $ids = Request::post('check');
            if (is_array($ids)) {
                switch (Request::post('action')) {
                    case 'disable':
                        Configurator::updateList($ids, ['enabled' => 0]);
                        break;
                    case 'enable':
                        Configurator::updateList($ids, ['enabled' => 1]);
                        break;
                    case 'delete':
                        foreach ($ids as $id) {
                            Configurator::deleteOne($id);
                        }
                        break;
                }
            }
        }

        Design::assign('configurators', Configurator::getList(order: 'position'));
        return $this->fetchAddonResponse('configurator_list.tpl');
    }
}
