<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.4
 *
 */

namespace App\Controller\Admin\Warehouse;

use HugaShop\Services\Design;
use HugaShop\Services\Helper;
use HugaShop\Services\Request;
use HugaShop\Models\Warehouse\WarehousePlace;
use App\Controller\BaseAdminController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PlaceListController extends BaseAdminController
{
    #[Route('/admin/warehouse/places', name: 'PlaceListAdmin')]
    public function index(): Response
    {

        $this->checkAdminAccess('warehouse_place');

        // Обработка действий
        if (Request::checkCSRF()) {

            // Действия с выбранными
            $ids = Request::post('check');
            if (is_array($ids)) {
                switch (Request::post('action')) {
                    case 'disable': {
                            WarehousePlace::updateList($ids, ['enabled' => 0]);
                            break;
                        }
                    case 'enable': {
                            WarehousePlace::updateList($ids, ['enabled' => 1]);
                            break;
                        }
                    case 'delete': {
                            WarehousePlace::deleteById($ids);
                            break;
                        }
                }
            }

            foreach (Helper::getPositions() as $id => $position) {
                WarehousePlace::updateOne($id, ['position' => $position]);
            }
        }

        Design::assign('places', WarehousePlace::getList(order: 'position'));

        return $this->fetchResponse('warehouse/place_list.tpl');
    }
}
