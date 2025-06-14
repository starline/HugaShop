<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.2
 *
 */

namespace App\Controller\Admin\Warehouse;

use HugaShop\Api\Design;
use HugaShop\Api\Request;
use HugaShop\Api\Warehouse\WarehousePlace;
use App\Controller\BaseAdminController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PlaceController extends BaseAdminController
{
    #[Route('/admin/warehouse/place', name: 'PlaceNewAdmin')]
    #[Route('/admin/warehouse/place/{id}', requirements: ['id' => '\d+'], name: 'PlaceAdmin')]
    public function index(?int $id = null): Response
    {

        $this->checkAdminAccess('warehouse_place');

        #### Update
        ###########
        if (!empty($place = Request::getDataAcces(WarehousePlace::getFields()))) {

            if (empty($place->id)) {
                $place = Design::setFlashMessage('add', WarehousePlace::create($place));
            } else {
                Design::setFlashMessage('update', WarehousePlace::updateOne($place->id, $place));
            }

            return $this->redirectToRoute('PlaceAdmin', ['id' => $place->id]);
        }


        #### View
        #########
        if (!empty($id)) {

            $place = WarehousePlace::find($id);

            if (empty($place->id)) {
                return $this->redirectToRoute('PlaceListAdmin');
            }
        }

        Design::assign('place', $place);

        return $this->fetchResponse('warehouse/place.tpl');
    }
}
