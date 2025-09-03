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
use HugaShop\Services\Helper;
use App\Controller\BaseAdminController;
use HugaShop\Addons\BaseAddonTrait;
use Symfony\Component\Routing\Attribute\Route;
use HugaShop\Addons\ProductConfigurator\Models\Configurator;
use HugaShop\Addons\ProductConfigurator\Models\ConfiguratorStep;

final class ConfiguratorController extends BaseAdminController
{
    use BaseAddonTrait;

    #[Route('/ProductConfigurator/configurator', name: 'AddonProductConfiguratorNew', priority: 20)]
    #[Route('/ProductConfigurator/configurator/{id}', name: 'AddonProductConfigurator', priority: 20)]
    public function configurator(?int $id = null)
    {
        $configurator = new \stdClass();

        if (Secure::checkCSRF()) {
            if ($positions = Helper::getPositions()) {
                foreach ($positions as $step_id => $position) {
                    ConfiguratorStep::updateOne($step_id, ['position' => $position]);
                }
            }

            if (!empty($config = Secure::getInputCheckEditAccess(Configurator::class, $id))) {
                if (empty($config->id)) {
                    $config = Design::setFlashMessage('add', Configurator::createOne($config));
                } else {
                    $config = Design::setFlashMessage('update', Configurator::updateOne($config->id, $config));
                }
                return $this->redirectToRoute('AddonProductConfigurator', ['id' => $config->id]);
            }
        }

        if (!empty($id)) {
            $configurator = Configurator::getOne($id);
            if (empty($configurator->id)) {
                return $this->redirectToRoute('AddonProductConfiguratorList');
            }
        }

        Design::assign('addon',         $this->getAddon());
        Design::assign('configurator',  $configurator);
        Design::assign('steps',         ConfiguratorStep::getList(['configurator_id' => $configurator->id ?? 0], order: 'position'));

        return $this->fetchAddonResponse('configurator.tpl');
    }
}
