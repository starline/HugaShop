<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 4.0
 *
 */

namespace App\Controller\Admin\Warehouse;

use stdClass;
use HugaShop\Api\Image;
use HugaShop\Api\Design;
use HugaShop\Api\Request;
use HugaShop\Api\User\User;
use HugaShop\Api\User\UserPermission;
use App\Controller\BaseAdminController;
use HugaShop\Api\Finance\FinancePayment;
use HugaShop\Api\Warehouse\WarehouseMove;
use HugaShop\Api\Warehouse\WarehousePlace;
use HugaShop\Api\Warehouse\WarehousePurchase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MoveController extends BaseAdminController
{
    #[Route('/admin/warehouse/movement', name: 'MoveNewAdmin')]
    #[Route('/admin/warehouse/movement/{id}', requirements: ['id' => '\d+'], name: 'MoveAdmin')]
    public function index(?int $id = null): Response
    {

        $this->checkAdminAccess('warehouse');

        $total =  new \stdClass();
        $total->purchases = 0;
        $payments = [];


        #### Update
        ###########
        if (!empty($movement = Request::getDataAcces(WarehouseMove::getFields()))) {

            // Создаем новую поставку/списание
            if (empty($movement->id)) {
                $movement = Design::setFlashMessage('add', WarehouseMove::addMovement($movement));
            } else {
                Design::setFlashMessage('update', WarehouseMove::updateMove($movement->id, $movement));
            }


            // TODO: Если разрешено только добавлять поставки,
            // можно редактировать свои поставки со статусом 0 (новое)
            // или свои поставки в течении сессии


            // Обновляем товары поставку
            if (!empty($movement->id)) {

                // Save Purchases
                $posted_purchase_ids = [];
                foreach (Request::post('purchases', 'array') as $position => $item) {

                    $item_upd = [
                        'product_id'    => $item['product_id'],
                        'amount'        => $item['amount'],
                        'position'      => $position,
                    ];

                    if (UserPermission::checkAccess("product_price")) {
                        $item_upd['cost_price'] = $item['cost_price'];
                    }

                    if (!empty($item['id'])) {
                        WarehousePurchase::updatePurchase($item['id'], $item_upd);
                        $posted_purchase_ids[] = $item['id'];
                    } else {
                        $item_upd['move_id'] = $movement->id;
                        $purchase = WarehousePurchase::addPurchase($item_upd);
                        $posted_purchase_ids[] = $purchase->id;
                    }
                }

                // Удаляем все purchase, которые были, но не пришли в POST (удалённые на фронте)
                $all_purchases = WarehousePurchase::getPurchases(['move_id' => $movement->id]);
                foreach ($all_purchases as $purchase) {
                    if (!in_array($purchase->id, $posted_purchase_ids)) {
                        WarehousePurchase::deletePurchase($purchase->id);
                    }
                }


                ////////////////////////////////////////////////
                // Cтатус перемещения, обновление склада товаров
                // Меняем статус поставки после того как purchases отредактированы
                ////////////////////////////////////////////////
                $move_status = Request::post('status', 'int');

                // Новый
                if ($move_status == 0) {
                    if (!WarehouseMove::open($movement->id)) {
                        Design::assign('message_error', 'error_open');
                    } else {
                        WarehouseMove::updateMove($movement->id, ['status' => 0]);
                    }
                }

                // Ожидаем
                elseif ($move_status === 1) {

                    // TODO: обновляем даты поставки. Также нужно проверять дату поставки, когда купили последний товар.

                    if (!WarehouseMove::open($movement->id)) {
                        Design::assign('message_error', 'error_open');
                    } else {
                        WarehouseMove::updateMove($movement->id, ['status' => 1]);
                    }
                }

                // Выполнен/добавлен на склад
                elseif ($move_status === 2) {
                    if (!WarehouseMove::close($movement->id)) {
                        Design::assign('message_error', 'error_closing');
                    } else {
                        WarehouseMove::updateMove($movement->id, ['status' => 2]);
                    }
                }

                // Списан
                elseif ($move_status === 3) {
                    if (!WarehouseMove::close($movement->id, true)) {
                        Design::assign('message_error', 'error_closing');
                    } else {
                        WarehouseMove::updateMove($movement->id, ['status' => 3]);
                    }
                }

                // Отменен
                elseif ($move_status === 4) {
                    if (!WarehouseMove::open($movement->id)) {
                        Design::assign('message_error', 'error_open');
                    } else {
                        WarehouseMove::updateMove($movement->id, ['status' => 4]);
                    }
                }
            }


            Image::catchImages($movement->id, 'warehouse', 'images');

            return $this->redirectToRoute('MoveAdmin', ['id' => $movement->id]);
        }


        #### View
        #########
        if (!empty($id)) {

            // Выбираем данные
            $movement = WarehouseMove::getMovement($id, ['images', 'purchases', 'purchases.warehouse_move', 'payments.category']);

            if (empty($movement->id)) {
                return $this->redirectToRoute('MoveListAdmin');
            }

            $total->cost_price = 0;
            $total->retail_price = 0;
            $total->weight = 0;
            $movement->payments->total_amount = 0;

            foreach ($movement->payments as $payment) {
                $sign = ($payment->type == 1) ? 1 : -1;
                $payment->amount = $sign * abs($payment->amount);

                $movement->payments->total_amount += $sign * $payment->currency_amount ?? $sign * $payment->amount;
            }

            // Выбранный Менеджер
            if (!empty($movement->manager_id)) {
                $movement->manager = User::getUser($movement->manager_id);
            }
        }

        //  Определяем возможность редактировать
        $can_edit = false;
        if ((in_array('warehouse_add', User::authUser('permissions')) and empty($movement->status)) or in_array('warehouse_edit', User::authUser('permissions'))) {
            $can_edit = true;
        }

        $warehouse_places = WarehousePlace::getList(['enabled' => 1], 'position');

        Design::assign([
            'movement'          => $movement,
            'total'             => $total,
            'can_edit'          => $can_edit,
            'warehouse_places'  => $warehouse_places
        ]);

        // Выводим на экран
        if (Request::get('type') === 'print') {
            return $this->fetchResponse('warehouse/move_print.tpl');
        }

        return $this->fetchResponse('warehouse/move.tpl');
    }
}
