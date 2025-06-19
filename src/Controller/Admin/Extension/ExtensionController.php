<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.8
 *
 */

namespace App\Controller\Admin\Extension;

use HugaShop\Api\Design;
use HugaShop\Api\Request;
use HugaShop\Api\Extension;
use App\Controller\BaseAdminController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ExtensionController extends BaseAdminController
{

    #[Route('/admin/extension/{module}', name: 'ExtensionAdmin', priority: 1)]
    public function index(string $module): Response
    {

        $this->checkAdminAccess('extension');

        if (empty($Extension = Extension::makeExtension($module))) {
            return $this->redirectToRoute('ExtensionListAdmin');
        }


        // Сохранить настройки
        if (!empty($extension_settings = Request::post('extension_settings', 'array'))) {
            Design::setFlashMessage('update', Extension::updateExt($module, $extension_settings));
            $Extension->ext_settings = (object) $extension_settings;
            return $this->redirectToRoute('ExtensionAdmin', ['module' => $module]);
        }


        Design::assign('extension', $Extension->ext_config);
        Design::assign('extension_settings', $Extension->ext_settings);

        if (method_exists($Extension, 'index')) {
            $Extension->setEnviroment('kernel', $this->container->get('kernel'));
            $template = $Extension->index();
        } else {
            $template = 'extension/extension.tpl';
        }

        return $this->fetchResponse($template);
    }


    #[Route('/admin/extension/{module}/{path}', name: 'ExtensionItemNewAdmin')]
    #[Route('/admin/extension/{module}/{path}/{item_id}', requirements: ['id' => '\d+', 'item_id' => '\d+'], name: 'ExtensionItemAdmin')]
    public function path(string $module, string $path, ?int $item_id = null): Response
    {

        $this->checkAdminAccess('extension');

        if (empty($module) || empty($Extension = Extension::makeExtension($module))) {
            return $this->redirectToRoute('ExtensionListAdmin');
        }

        Design::assign('extension', $Extension->ext_config);
        Design::assign('extension_settings', $Extension->ext_settings);

        return $this->fetchResponse($Extension->$path($item_id));
    }
}
