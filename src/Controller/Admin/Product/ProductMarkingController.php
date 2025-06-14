<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.1
 * 
 * ProductMaarkingAdmin
 *
 */

namespace App\Controller\Admin\Product;

use HugaShop\Api\Design;
use HugaShop\Api\Request;
use HugaShop\Api\Product\Product;
use App\Controller\BaseAdminController;
use HugaShop\Api\Product\ProductVariant;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProductMarkingController extends BaseAdminController
{
    #[Route('/admin/product/{variant_id}/marking', requirements: ['variant_id' => '\d+'], name: 'ProductMarkingAdmin')]
    public function index(int $variant_id): Response
    {

        $this->checkAdminAccess('product_marking');

        $variant = ProductVariant::getVariant(intval($variant_id));

        if (empty($variant->product_id)) {
            return $this->redirectToRoute('ProductListAdmin');
        }

        $product = Product::getProduct(intval($variant->product_id));
        $variant->product_name = $product->name;

        Design::assign('variant', $variant);
        Design::assign('count', Request::getVar('count', 'int') ?: 1);

        // Выводим на экран
        return $this->fetchResponse('product/product_marking_print.tpl');
    }
}
