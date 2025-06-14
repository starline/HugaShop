<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.2
 * 
 * ProductMaarkingAdmin
 *
 */

namespace App\Controller\Admin\Product;

use HugaShop\Api\Design;
use HugaShop\Api\Request;
use HugaShop\Api\Product\Product;
use App\Controller\BaseAdminController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProductMarkingController extends BaseAdminController
{
    #[Route('/admin/product/{product_id}/marking', requirements: ['product_id' => '\d+'], name: 'ProductMarkingAdmin')]
    public function index(int $product_id): Response
    {

        $this->checkAdminAccess('product_marking');

        $product = Product::getOne($product_id);

        if (empty($product->id)) {
            return $this->redirectToRoute('ProductListAdmin');
        }

        Design::assign('product', $product);
        Design::assign('count', Request::getVar('count', 'int') ?: 1);

        // Выводим на экран
        return $this->fetchResponse('product/product_marking_print.tpl');
    }
}
