<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.2
 *
 */

namespace App\Controller\Admin\Extension;

use HugaShop\Services\Design;
use HugaShop\Services\Request;
use HugaShop\Services\Extension;
use App\Controller\BaseAdminController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

class ExtensionController extends BaseAdminController
{

    #[Route('/admin/extension/{name}', name: 'ExtensionAdmin', priority: 1)]
    public function index(string $name): Response
    {

        $this->checkAdminAccess('extension');

        if (empty($Extension = Extension::makeExtension($name))) {
            return $this->redirectToRoute('ExtensionListAdmin');
        }

        if (!$Extension->hasIndex()) {
            return $this->redirectToRoute('ExtensionSettingsAdmin', ['name' => $Extension->getName()]);
        }

        $Extension->setEnvironment('kernel', $this->container->get('kernel'));
        Design::assign('extension', $Extension->getExtension());

        // Ajax
        if (Request::isAjax()) {
            return $Extension->index();
        }

        return $this->fetchResponse($Extension->index());
    }


    #[Route('/admin/extension/{name}/{path}', name: 'ExtensionItemNewAdmin')]
    #[Route('/admin/extension/{name}/{path}/{item_id}', requirements: ['id' => '\d+', 'item_id' => '\d+'], name: 'ExtensionItemAdmin')]
    public function path(string $name, string $path, ?int $item_id = null): Response
    {

        $this->checkAdminAccess('extension');

        if (empty($name) || empty($Extension = Extension::makeExtension($name))) {
            return $this->redirectToRoute('ExtensionListAdmin');
        }

        Design::assign('extension', $Extension->getExtension());

        return $this->fetchResponse($Extension->$path($item_id));
    }


    /**
     * Ajax Request
     */
    #[Route('/admin/extension/{name}/ajax/{path}', name: 'ExtensionAjaxAdmin', priority: 2)]
    public function ajax(string $name, string $path): Response
    {

        $this->checkAdminAccess('extension');

        if (empty($name) || empty($Extension = Extension::makeExtension($name))) {
            return $this->redirectToRoute('ExtensionListAdmin');
        }

        Design::assign('extension', $Extension->getExtension());

        return new JsonResponse($Extension->$path());
    }


    /**
     * Get Settings page
     */
    #[Route('/admin/extension/{name}/settings', name: 'ExtensionSettingsAdmin', priority: 2)]
    public function settingsPage(string $name): Response
    {

        $this->checkAdminAccess('extension');

        if (empty($Extension = Extension::makeExtension($name))) {
            return $this->redirectToRoute('ExtensionListAdmin');
        }

        // Сохранить настройки
        if (!empty($extension_settings = Request::post('extension_settings', 'array'))) {
            Design::setFlashMessage('update', Extension::updateExt($Extension->getName(), $extension_settings));
            return $this->redirectToRoute('ExtensionSettingsAdmin', ['name' => $Extension->getName()]);
        }

        Design::assign('extension', $Extension->getExtension());
        Design::assign('extensions', [$Extension->getName() => $Extension->getConfig()]);

        return $this->fetchResponse('extension/extension.tpl');
    }
}
