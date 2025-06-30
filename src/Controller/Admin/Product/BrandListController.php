<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.0
 *
 */

namespace App\Controller\Admin\Product;

use HugaShop\Services\Design;
use HugaShop\Models\Request;
use HugaShop\Models\Product\ProductBrand;
use HugaShop\Models\User\UserPermission;
use App\Controller\BaseAdminController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class BrandListController extends BaseAdminController
{
    #[Route('/admin/product/brands', name: 'BrandListAdmin')]
    public function index(): Response
    {

        $this->checkAdminAccess('product_brand');

        // Обработка действий
        if (Request::checkCSRF() and UserPermission::checkAccess(['product_brand_delete'])) {

            // Действия с выбранными
            $ids = Request::post('check');

            if (is_array($ids)) {
                switch (Request::post('action')) {
                    case 'delete': {
                            foreach ($ids as $id) {
                                ProductBrand::deleteBrand($id);
                            }
                            break;
                        }
                }
            }
        }

        $brands = ProductBrand::getBrands();
        Design::assign('brands', $brands);

        return $this->fetchResponse('product/brand_list.tpl');
    }
}
