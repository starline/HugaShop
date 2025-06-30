<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.1
 *
 */

namespace App\Controller\Admin\Warehouse;

use HugaShop\Services\Design;
use HugaShop\Models\Request;
use HugaShop\Models\Product\ProductProvider;
use App\Controller\BaseAdminController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProviderListController extends BaseAdminController
{
    #[Route('/admin/warehouse/providers', name: 'ProviderListAdmin')]
    public function index(): Response
    {

        $this->checkAdminAccess('warehouse_provider');

        // Обработка действий
        if (Request::checkCSRF()) {

            // Действия с выбранными
            $ids = Request::post('check');
            if (is_array($ids)) {
                switch (Request::post('action')) {
                    case 'delete': {
                            ProductProvider::deleteOne($ids);
                            break;
                        }
                }
            }
        }

        Design::assign('providers', ProductProvider::all());

        return $this->fetchResponse('warehouse/provider_list.tpl');
    }
}
