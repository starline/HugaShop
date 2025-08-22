<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.3
 *
 */

namespace App\Controller\Admin\Addon;

use HugaShop\Services\Design;
use HugaShop\Services\Addon;
use HugaShop\Services\Request;
use App\Controller\BaseAdminController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AddonListController extends BaseAdminController
{
    #[Route('/admin/addons', name: 'AddonListAdmin')]
    public function index(): Response
    {

        $this->checkAdminAccess('addon');

        $addon_modules = Addon::getAddonsList();

        if ($keyword = Request::get('keyword', 'string')) {
            $addon_modules = array_filter(
                $addon_modules,
                fn($m) => (mb_stripos($m->name, $keyword) !== false)
                    || (mb_stripos($m->description, $keyword) !== false)
            );
            Design::assign('keyword', $keyword);
        }

        Design::assign('addon_modules', $addon_modules);

        return $this->fetchResponse('addon/addon_list.tpl');
    }
}
