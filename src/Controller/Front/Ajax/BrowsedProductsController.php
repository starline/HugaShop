<?php

namespace App\Controller\Front\Ajax;

use App\Controller\BaseFrontController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;

class BrowsedProductsController extends BaseFrontController
{
    #[Route('/ajax/browsed-products', name: 'BrowsedProductsBlock', priority: 1)]
    public function browsedProducts(): Response
    {
        return $this->fetchResponse('parts/browsed_products.tpl', 'browsed_products');
    }
}
