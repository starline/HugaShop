<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.1
 *
 */

namespace App\Controller\Admin\Extension;

use HugaShop\Services\Design;
use HugaShop\Services\Extension;
use App\Controller\BaseAdminController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ExtensionListController extends BaseAdminController
{
    #[Route('/admin/extensions', name: 'ExtensionListAdmin')]
    public function index(): Response
    {

        $this->checkAdminAccess('extension');

        $extension_modules = Extension::getExtensionsList();
        Design::assign('extension_modules', $extension_modules);

        return $this->fetchResponse('extension/extension_list.tpl');
    }
}
