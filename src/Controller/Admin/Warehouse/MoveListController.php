<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.2
 *
 */

namespace App\Controller\Admin\Warehouse;

use HugaShop\Models\Design;
use HugaShop\Models\Request;
use App\Services\PaginationService;
use HugaShop\Models\Warehouse\WarehouseMove;
use HugaShop\Models\User\UserPermission;
use App\Controller\BaseAdminController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MoveListController extends BaseAdminController
{
    #[Route('/admin/warehouse/moves', name: 'MoveListAdmin')]
    public function index(): Response
    {

        $this->checkAdminAccess('warehouse');

        // Обработка действий
        if (Request::checkCSRF() and UserPermission::checkAccess("warehouse_edit")) {

            // Действия с выбранными
            $ids = Request::post('check', 'array');
            if (is_array($ids)) {
                switch (Request::post('action')) {
                    case 'delete': {
                            foreach ($ids as $id) {
                                WarehouseMove::deleteMovement($id);
                            }
                            break;
                        }
                }
            }
        }

        $filter = PaginationService::initFilter();
        $filter['status'] = Request::getInt('status'); # Тип перемещения

        // Поиск
        $keyword = Request::get('keyword', 'string');
        if (!empty($keyword)) {
            $filter['keyword'] = $keyword;
            Design::assign('keyword', $keyword);
        }

        $movements =        WarehouseMove::getMovements($filter, join: ['images', 'purchases', 'purchases.product', 'purchases.product.image']); # Выбираем все поставки
        $movements_count =  WarehouseMove::countMovements($filter);

        // Собираем статистические данные
        $total = new \stdClass();
        $total->cost_price = 0;
        $total->retail_price = 0;
        $total->product_amount = 0;

        if ($movements->isNotEmpty()) {

            // TODO: сделай подсчет общей себестоимости всех выбранных поставок(cost_price) 
            // Обзей розничной стоимости (retail_price)
            // Кол-во единиц товара во всех выбранных поставках (product_amount)
            // только для status=0 и status=1

        }

        Design::assign('total', $total);

        Design::assign('pagination', PaginationService::getPagination($movements_count, $filter));

        Design::assign('movements', $movements);
        Design::assign('movements_count', $movements_count);
        Design::assign('status', $filter['status']);

        return $this->fetchResponse('warehouse/move_list.tpl');
    }
}
