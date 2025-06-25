<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.9
 *
 */

namespace App\Controller\Admin\Extension;

use HugaShop\Models\Design;
use HugaShop\Models\Request;
use HugaShop\Models\Extension;
use App\Controller\BaseAdminController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ExtensionController extends BaseAdminController
{

    #[Route('/admin/extension/{name}', name: 'ExtensionAdmin', priority: 1)]
    public function index(string $name): Response
    {

        $this->checkAdminAccess('extension');

        if (empty($Extension = Extension::makeExtension($name))) {
            return $this->redirectToRoute('ExtensionListAdmin');
        }

        $extension            = clone $Extension->getConfig();
        $extension->settings  = $Extension->ext_settings;
        Design::assign('extension', $extension);

        if (method_exists($Extension, 'index')) {
            $Extension->setEnvironment('kernel', $this->container->get('kernel'));

            // Ajax
            if (Request::isAjax()) {
                return $Extension->index();
            }

            return $this->fetchResponse($Extension->index());
        } else {
            return $this->settings($Extension);
        }
    }


    #[Route('/admin/extension/{name}/{path}', name: 'ExtensionItemNewAdmin')]
    #[Route('/admin/extension/{name}/{path}/{item_id}', requirements: ['id' => '\d+', 'item_id' => '\d+'], name: 'ExtensionItemAdmin')]
    public function path(string $name, string $path, ?int $item_id = null): Response
    {

        $this->checkAdminAccess('extension');

        if (empty($name) || empty($Extension = Extension::makeExtension($name))) {
            return $this->redirectToRoute('ExtensionListAdmin');
        }

        Design::assign('extension', $Extension->ext_config);
        Design::assign('extension_settings', $Extension->ext_settings);

        return $this->fetchResponse($Extension->$path($item_id));
    }


    /**
     * Get Settings page
     */
    private function settings($Extension)
    {

        // Сохранить настройки
        if (!empty($extension_settings = Request::post('extension_settings', 'array'))) {
            Design::setFlashMessage('update', Extension::updateExt($Extension->getName(), $extension_settings));
            return $this->redirectToRoute('ExtensionAdmin', ['name' => $Extension->getName()]);
        }

        Design::assign('extensions', [$Extension->getName() => $Extension->getConfig()]);

        return $this->fetchResponse('extension/extension.tpl');
    }
}
