<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.0
 *
 */

namespace App\Controller\Admin;

use HugaShop\Models\Config;
use App\Controller\BaseAdminController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class InstallController extends BaseAdminController
{
    
    #[Route('/install', name: 'InstallAdmin')]
    public function index(): Response
    {
        if (empty(Config::get('database')->name)) {
            return $this->redirectToRoute('MainAdmin');
        }

        return $this->fetchResponse('install.tpl');
    }
}
