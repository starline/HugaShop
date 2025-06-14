<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.1
 *
 */

namespace App\Controller\Admin\Warehouse;

use HugaShop\Api\Design;
use HugaShop\Api\Request;
use HugaShop\Api\Settings;
use HugaShop\Api\Warehouse\WarehouseMove;
use HugaShop\Api\User\UserPermission;
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
            $ids = Request::post('check');
            if (is_array($ids)) {
                switch (Request::post('action')) {
                    case 'delete': {
                            foreach ($ids as $id) {
                                $whm = WarehouseMove::getMovement(intval($id));

                                // Удалять можно только отмененный (4)
                                if ($whm->status == 4) {
                                    WarehouseMove::deleteMovement(intval($whm->id));
                                }
                            }
                            break;
                        }
                }
            }
        }

        $filter = [];
        $filter['page'] = max(1, Request::get('page', 'int'));
        $filter['limit'] = Request::get('page', 'string') == 'all' ? 'all' : Settings::getParam('products_num_admin');
        $filter['status'] = Request::get('status', 'integer'); # Тип перемещения

        // Поиск
        $keyword = Request::get('keyword', 'string');
        if (!empty($keyword)) {
            $filter['keyword'] = $keyword;
            Design::assign('keyword', $keyword);
        }

        $movements =        WarehouseMove::getMovements($filter, join: ['images', 'purchases']); # Выбираем все поставки
        $movements_count =  WarehouseMove::countMovements($filter);

        // Собираем статистические данные
        $total = new \stdClass();
        $total->cost_price = 0;
        $total->retail_price = 0;
        $total->product_amount = 0;

        if ($movements->isNotEmpty()) {

            // Товары
           /* foreach (WarehousePurchase::getPurchases(['move_id' => array_keys($movements)], ["image"]) as $move_p) {
                $movements[$move_p->move_id]->purchases[] = $move_p;

                $total->product_amount += $move_p->amount;
                $total->retail_price += $move_p->price * $move_p->amount;
                $total->cost_price += $move_p->cost_price * $move_p->amount;
            }

            $total->await_movements_count = $movements_count;*/
        }

        Design::assign('total', $total);

        Design::assign('pages_count', ceil($movements_count / Settings::getParam('products_num_admin')));
        Design::assign('current_page', $filter['limit'] == 'all' ? 'all' : $filter['page']);

        Design::assign('movements', $movements);
        Design::assign('movements_count', $movements_count);
        Design::assign('status', $filter['status']);

        return $this->fetchResponse('warehouse/move_list.tpl');
    }
}
