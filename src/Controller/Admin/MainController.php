<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.4
 *
 */

namespace App\Controller\Admin;

use App\Controller\BaseAdminController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MainController extends BaseAdminController
{
    #[Route('/admin', name: 'MainAdmin')]
    public function index(): Response
    {
        return $this->redirectToRoute('OrderListAdmin');
    }
}
