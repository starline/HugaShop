<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.4
 *
 */

namespace App\Controller\Admin\Addon;

use HugaShop\Services\Design;
use HugaShop\Services\Request;
use HugaShop\Services\Addon;
use App\Controller\BaseAdminController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AddonController extends BaseAdminController
{

    /**
     * Redirect to settings
     */
    #[Route('/addon/{name}', name: 'AddonAdmin')]
    public function index(string $name): Response
    {
        return $this->redirectToRoute('AddonSettingsAdmin', ['name' => $name]);
    }


    /**
     * Get Settings page
     */
    #[Route('/addon/{name}/settings', name: 'AddonSettingsAdmin', priority: 30)]
    public function settingsPage(string $name): Response
    {

        $this->checkAdminAccess('addon');

        if (empty($Ext = Addon::getNameSpace($name))) {
            return $this->redirectToRoute('AddonListAdmin');
        }

        // Сохранить настройки
        if (!empty($addon_settings = Request::post('addon_settings', 'array'))) {
            Design::setFlashMessage('update', Addon::updateExt($Ext::getName(), $addon_settings));
            return $this->redirectToRoute('AddonSettingsAdmin', ['name' => $Ext::getName()]);
        }

        Design::assign('addon', $Ext::getAddon());
        Design::assign('addons', [$Ext::getName() => $Ext::getConfig()]);

        return $this->fetchResponse('addon/addon.tpl');
    }
}
