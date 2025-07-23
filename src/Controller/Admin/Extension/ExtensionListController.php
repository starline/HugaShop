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
use HugaShop\Services\Extension;
use HugaShop\Services\Request;
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

        if ($keyword = Request::get('keyword', 'string')) {
            $extension_modules = array_filter(
                $extension_modules,
                fn($m) => (mb_stripos($m->name, $keyword) !== false)
                    || (mb_stripos($m->description, $keyword) !== false)
            );
            Design::assign('keyword', $keyword);
        }

        Design::assign('extension_modules', $extension_modules);

        return $this->fetchResponse('extension/extension_list.tpl');
    }
}
