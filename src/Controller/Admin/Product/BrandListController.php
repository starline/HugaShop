<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.2
 *
 */

namespace App\Controller\Admin\Product;

use HugaShop\Services\Design;
use HugaShop\Services\Secure;
use HugaShop\Services\Request;
use App\Controller\BaseAdminController;
use HugaShop\Models\User\UserPermission;
use HugaShop\Models\Product\ProductBrand;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class BrandListController extends BaseAdminController
{
    #[Route('/admin/product/brands', name: 'BrandListAdmin')]
    public function index(): Response
    {

        $this->checkAdminAccess('product_brand');

        // Обработка действий
        if (Secure::checkCSRF() and UserPermission::checkAccess(['product_brand_delete'])) {

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

        Design::assign('brands', ProductBrand::getBrands(join: ['image']));
        return $this->fetchResponse('product/brand_list.tpl');
    }
}
