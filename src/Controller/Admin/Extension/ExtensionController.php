<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.3
 *
 */

namespace App\Controller\Admin\Extension;

use HugaShop\Services\Design;
use HugaShop\Services\Request;
use HugaShop\Services\Extension;
use App\Controller\BaseAdminController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ExtensionController extends BaseAdminController
{

    /**
     * Redirect to settings
     */
    #[Route('/admin/extension/{name}', name: 'ExtensionAdmin')]
    public function index(string $name): Response
    {
        return $this->redirectToRoute('ExtensionSettingsAdmin', ['name' => $name]);
    }


    /**
     * Get Settings page
     */
    #[Route('/admin/extension/{name}/settings', name: 'ExtensionSettingsAdmin', priority: 30)]
    public function settingsPage(string $name): Response
    {

        $this->checkAdminAccess('extension');

        if (empty($Ext = Extension::getNameSpace($name))) {
            return $this->redirectToRoute('ExtensionListAdmin');
        }

        // Сохранить настройки
        if (!empty($extension_settings = Request::post('extension_settings', 'array'))) {
            Design::setFlashMessage('update', Extension::updateExt($Ext::getName(), $extension_settings));
            return $this->redirectToRoute('ExtensionSettingsAdmin', ['name' => $Ext::getName()]);
        }

        Design::assign('extension', $Ext::getExtension());
        Design::assign('extensions', [$Ext::getName() => $Ext::getConfig()]);

        return $this->fetchResponse('extension/extension.tpl');
    }
}
