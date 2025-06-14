<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 3.9
 *
 */

namespace App\Controller\Admin\Warehouse;

use stdClass;
use HugaShop\Api\User\User;
use HugaShop\Api\Image;
use HugaShop\Api\Design;
use HugaShop\Api\Request;
use HugaShop\Api\Warehouse\WarehouseMove;
use HugaShop\Api\Finance\FinancePayment;
use HugaShop\Api\Product\ProductVariant;
use HugaShop\Api\User\UserPermission;
use HugaShop\Api\Warehouse\WarehousePlace;
use HugaShop\Api\Warehouse\WarehousePurchase;
use App\Controller\BaseAdminController;
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
        $purchases = [];


        #### Update
        ###########
        if (!empty($movement = Request::getDataAcces(WarehouseMove::getFields()))) {

            // Создаем новую поставку/списание
            if (empty($movement->id)) {

                // Закрепляем за менеджером
                $movement->manager_id = User::authUser('id');
                $movement = Design::setFlashMessage('add', WarehouseMove::addMovement($movement));
            } else {
                Design::setFlashMessage('update', WarehouseMove::updateMove($movement->id, $movement));
            }


            // TODO: Если разрешено только добавлять поставки,
            // можно редактировать свои поставки со статусом 0 (новое)
            // или свои поставки в течении сессии


            // Обновляем товары поставку
            if (!empty($movement->id)) {

                // Закупки/списание товаров
                if (Request::post('purchases')) {
                    foreach (Request::post('purchases') as $n => $var) { # id, variant_id, amount
                        foreach ($var as $i => $v) {
                            if (empty($purchases[$i])) {
                                $purchases[$i] = new \stdClass();
                            }
                            $purchases[$i]->$n = $v;
                        }
                    }
                }

                $posted_purchases_ids = [];
                foreach ($purchases as $purchase) {
                    $variant = ProductVariant::getVariant($purchase->product_id);

                    $purchase_upd = new stdClass();
                    $purchase_upd->amount = $purchase->amount;

                    // Обновляем существующий вариант товара
                    if (!empty($purchase->id)) {
                        if (!empty($variant)) { # если исходный вариант существует

                            // Если параметр не задан, берется с исходного варианта товара
                            $purchase_upd->variant_id = $purchase->variant_id;
                            $purchase_upd->variant_name = $variant->name;
                            $purchase_upd->sku = $variant->sku;

                            if (UserPermission::checkAccess("product_price") and isset($purchase->cost_price)) {
                                $purchase_upd->cost_price = $purchase->cost_price;
                            }
                        } else { # Если исходный вариант удален, не существует
                            if (UserPermission::checkAccess("product_price") and isset($purchase->cost_price)) {
                                $purchase_upd->cost_price = $purchase->cost_price;
                            }
                        }

                        WarehousePurchase::updatePurchase($purchase->id, $purchase_upd);
                    }

                    // Добавляем новый вариант товара
                    else {
                        $purchase_upd->move_id = $movement->id;
                        $purchase_upd->variant_id = $purchase->variant_id;
                        if (UserPermission::checkAccess("product_price") and isset($purchase->cost_price)) {
                            $purchase_upd->cost_price = $purchase->cost_price;
                        }
                        $purchase->id = WarehousePurchase::addPurchase($purchase_upd);
                    }

                    $posted_purchases_ids[] = $purchase->id;
                }

                // Удалить непереданные товары
                foreach (WarehousePurchase::getPurchases(['move_id' => $movement->id]) as $purch_temp) {
                    if (!in_array($purch_temp->id, $posted_purchases_ids)) {
                        WarehousePurchase::deletePurchase($purch_temp->id);
                    }
                }

                // Отсортировать  варианты
                asort($posted_purchases_ids);
                $i = 0;
                foreach ($posted_purchases_ids as $purchase_id) {
                    WarehousePurchase::updatePurchase($posted_purchases_ids[$i], ['position' => $purchase_id]);
                    $i++;
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
                elseif ($move_status == 1) {

                    // TODO: обновляем даты поставки. Также нужно проверять дату поставки, когда купили последний товар.

                    if (!WarehouseMove::open($movement->id)) {
                        Design::assign('message_error', 'error_open');
                    } else {
                        WarehouseMove::updateMove($movement->id, ['status' => 1]);
                    }
                }

                // Выполнен/добавлен на склад
                elseif ($move_status == 2) {
                    if (!WarehouseMove::close($movement->id)) {
                        Design::assign('message_error', 'error_closing');
                    } else {
                        WarehouseMove::updateMove($movement->id, ['status' => 2]);
                    }
                }

                // Списан
                elseif ($move_status == 3) {
                    if (!WarehouseMove::close($movement->id, true)) {
                        Design::assign('message_error', 'error_closing');
                    } else {
                        WarehouseMove::updateMove($movement->id, ['status' => 3]);
                    }
                }

                // Отменен
                elseif ($move_status == 4) {
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
            $movement = WarehouseMove::getMovement($id, ['images', 'purchases']);

            if (empty($movement->id)) {
                return $this->redirectToRoute('MoveListAdmin');
            }

            $total->cost_price = 0;
            $total->retail_price = 0;
            $total->weight = 0;
            $total->payments_price = 0;

            //  Выбираем платежи
            $rel_payments = FinancePayment::getWarehousePayments($movement->id);
            foreach ($rel_payments as $pay) {
                $payment = FinancePayment::getPayment($pay->payment_id);

                // Учитываем расход или приход (#expense or income)
                $number_sign = ($payment->type == 0) ? -1 : 1;
                $total->payments_price +=  $number_sign * $payment->currency_amount ??  $number_sign * $payment->amount;
                $payment->amount = $number_sign * $payment->amount;

                $payments[$payment->id] = $payment;
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
            'payments'          => $payments,
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
