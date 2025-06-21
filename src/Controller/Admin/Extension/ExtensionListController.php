<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.0
 *
 */

namespace App\Controller\Admin\Extension;

use HugaShop\Models\Design;
use HugaShop\Models\Extension;
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
