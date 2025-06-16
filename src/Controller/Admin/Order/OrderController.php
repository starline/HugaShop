<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.4
 *
 */

namespace App\Controller\Admin\Order;

use stdClass;
use HugaShop\Api\Design;
use HugaShop\Api\Request;
use HugaShop\Api\Settings;
use HugaShop\Api\Cart\Cart;
use HugaShop\Api\User\User;
use App\Event\OrderAddEvent;
use HugaShop\Api\Order\Order;
use HugaShop\Api\Order\OrderLabel;
use HugaShop\Api\User\UserNotifier;
use HugaShop\Api\Order\OrderPayment;
use HugaShop\Api\Order\OrderDelivery;
use HugaShop\Api\Order\OrderPurchase;
use HugaShop\Api\User\UserPermission;
use HugaShop\Api\Finance\FinancePurse;
use App\Controller\BaseAdminController;
use HugaShop\Api\Finance\FinancePayment;
use HugaShop\Api\Finance\FinanceCurrency;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use HugaShop\Api\Finance\FinancePaymentContractor;

class OrderController extends BaseAdminController
{
    #[Route('/admin/order', name: 'OrderNewAdmin')]
    #[Route('/admin/order/{id}', requirements: ['id' => '\d+'], name: 'OrderAdmin')]
    public function index(?int $id = null): Response
    {

        $this->checkAdminAccess('order');

        #### Update
        ###########
        if (!empty($order = Request::getDataAcces(Order::getFields()))) {

            // Если выбрали "заказ оплачен", но не выбрали способ оплаты - отменим оплату
            if (!empty($order->paid) and !$order->payment_method_id) {
                Design::assign('message_error', 'error_paid');
                $order->paid = 0;
            }

            // Преобразуем настройки модулей в json
            $order->settings = Request::post('order_settings', 'array');

            // Определяем покупателя по телефону/email, если нет, добавляем нового
            if (empty($order->user_id and (!empty($order->phone) || !empty($order->email)))) {

                // Выбираем пользователя по номеру телефона
                if (!empty($order->phone) and !empty($user = User::getUser(['phone' => $order->phone]))) {
                    $order->user_id = $user->id;
                }

                // Выбираем пользователя по email
                elseif (!empty($order->email) and !empty($user = User::getUser(['email' => $order->email]))) {
                    $order->user_id = $user->id;
                }

                // если не найден, создаем нового
                elseif (!empty($order->name)) {
                    $user = new \stdClass();
                    $user->name = $order->name;
                    $user->email = $order->email;
                    $user->phone = $order->phone;
                    $user->enabled = 1;
                    $order->user_id = User::addUser($user);
                }
            }


            // Создаем новый заказ
            if (empty($order->id)) {

                // Новый, созданый заказ закрепляем за менеджером
                $order->manager_id = User::authUser()->id;

                // Определяем оплату доставки
                if (!empty($order->delivery_id)) {
                    $delivery_method = OrderDelivery::getOne($order->delivery_id);
                    if (empty($order->separate_delivery) and !empty($delivery_method->separate_payment)) {
                        $order->separate_delivery = $delivery_method->separate_payment;
                    }
                    if (empty($order->delivery_price)) {
                        $order->delivery_price = $delivery_method->price;
                    }
                }

                $order = Design::setFlashMessage('add', Order::addOrder($order));
            }

            // Обновляем заказ
            else {
                if (empty($order->manager_id)) {
                    $order->manager_id = User::authUser()->id; # Пользователь текущей сессии
                }

                Design::setFlashMessage('update', Order::updateOrder($order->id, $order));
            }

            // Обновляем метки заказа
            $order_labels = Request::post('order_labels', 'array') ?: [];
            OrderLabel::updateOrderLabels($order->id, $order_labels);


            // Save Purchases
            $posted_purchase_ids = [];
            foreach (Request::post('purchases', 'array') as $position => $item) {

                $item_upd = [
                    'product_id'    => $item['product_id'],
                    'amount'        => $item['amount'],
                    'position'      => $position,
                ];

                if (UserPermission::checkAccess("product_price")) {
                    $item_upd['price'] = $item['price'];
                }

                if (!empty($item['id'])) {
                    OrderPurchase::updatePurchase($item['id'], $item_upd);
                    $posted_purchase_ids[] = $item['id'];
                } else {
                    $item_upd['order_id'] = $order->id;
                    $purchase = OrderPurchase::addPurchase($item_upd);
                    $posted_purchase_ids[] = $purchase->id;
                }
            }

            // Удаляем все purchase, которые были, но не пришли в POST (удалённые на фронте)
            $all_purchases = OrderPurchase::getPurchases(['order_id' => $order->id]);
            foreach ($all_purchases as $purchase) {
                if (!in_array($purchase->id, $posted_purchase_ids)) {
                    OrderPurchase::deletePurchase($purchase->id);
                }
            }

            // Обновляем общую стоимость и прибыль, комиссию менеджера
            Order::updateTotalPrice($order->id, false);


            ////////////////////////////////////////////
            // Статус заказа, обновление склада товаров
            ///////////////////////////////////////////
            $order_status = Request::post('status', 'string');


            if ($order_status == 0) { # Новый
                if (!Order::open(intval($order->id))) {
                    Design::assign('message_error', 'error_open');
                } else {
                    Order::updateOne($order->id, ['status' => 0]);
                }
            } elseif ($order_status == 1) { # Принят
                if (!Order::close(intval($order->id))) {
                    Design::assign('message_error', 'error_closing');
                } else {
                    Order::updateOne($order->id, ['status' => 1]);
                }
            } elseif ($order_status == 4) { # Отгружен
                if (!Order::close(intval($order->id))) {
                    Design::assign('message_error', 'error_closing');
                } else {
                    Order::updateOne($order->id, ['status' => 4]);
                }
            } elseif ($order_status == 2) { # Выполнен
                if (!Order::close(intval($order->id))) {
                    Design::assign('message_error', 'error_closing');
                } else {
                    Order::updateOne($order->id, ['status' => 2]);
                }
            } elseif ($order_status == 3) { # Отмена
                if (!Order::open(intval($order->id))) {
                    Design::assign('message_error', 'error_open');
                } else {
                    Order::updateOne($order->id, ['status' => 3]);
                }
            }

            // Выбираем даные по заказу.
            $order = Order::getOrder(intval($order->id));



            //////////////////////////////
            // Создаем платежи в Финансах автоматически
            // Для заказов после даты обновления алгоритма
            /////////////////////////////
            if (strtotime($order->date) > strtotime('2024-03-01')) {

                // Платеж по заказу (Выручка). Если заказ оплачен и выбран способ оплаты и сумма оплаты > 0
                $order_payment_income = FinancePayment::getOrderPayment($order->id, 'income');
                if (!empty($order->paid) and !empty($order->payment_method_id) and $order->payment_price > 0) {

                    $payment_method = OrderPayment::getOne($order->payment_method_id);

                    // В настройках способа оплаты должжен быть указан колешел
                    if (!empty($payment_method->finance_purse_id)) {

                        $payment_income = new \stdClass();
                        $payment_income->finance_category_id = Settings::getParam('income_finance_category_id');    # Категория платежжа. Выручка
                        $payment_income->type = 1;                                                                  # Тип платежа. Приход
                        $payment_income->manager_id = User::authUser('id');                                         # Пользователь из сессии
                        $payment_income->purse_id = $payment_method->finance_purse_id;                              # Выбрать соответсвующий кошелек

                        // Пересчитываем финансовый платеж. Если настройка задана в способе оплаты
                        $payment_income_calculate = $order->payment_price;
                        if (!empty($payment_method->settings->calculate_finance_payment)) {
                            if (!empty($payment_method->settings->fee_inside)) {
                                $payment_income_calculate = $payment_income_calculate - $payment_income_calculate * ($payment_method->settings->fee_inside / 100);
                            }

                            // Сначало вычисляем проценты, затем отнимаем платежи
                            if (!empty($payment_method->settings->fee_fix_inside)) {
                                $payment_income_calculate = $payment_income_calculate - $payment_method->settings->fee_fix_inside;
                            }
                        }

                        // Переводим в валюту кошелька
                        $payment_method_purse = FinancePurse::getOne($payment_method->finance_purse_id);
                        $payment_income->amount = FinanceCurrency::priceConvert($payment_income_calculate, intval($payment_method_purse->currency_id), false);

                        // Если платеж уже внесен в финансы
                        if (!empty($order_payment_income->id)) {

                            // Обновляем. Если платеж не сверен бухгалтером
                            if (empty($order_payment_income->verified)) {
                                $payment_income->id = $order_payment_income->id;
                                FinancePayment::updatePayment($payment_income->id, $payment_income);
                            }
                        } else {
                            $payment_income->id = FinancePayment::addPayment($payment_income);

                            // Добавляем контрагента "заказ"
                            $contractor = new \stdClass();
                            $contractor->payment_id =   $payment_income->id;
                            $contractor->entity_id =    $order->id;
                            $contractor->entity_name =  'order';
                            FinancePaymentContractor::addContractor($contractor);
                        }
                    }
                }

                // Удалим платеж (приход)
                // Eсли заказа не оплачен и не сверен или не выбран способ оплаты или не выбран кошелек оплаты
                if (!empty($order_payment_income->id) and empty($order_payment_income->verified) and (empty($order->paid) || empty($order->payment_method_id) || empty($payment_method->finance_purse_id))) {
                    FinancePayment::deletePayment($order_payment_income->id);
                }


                // Расход на доставку. Если в стоимость заказа включена доставка. Заказ оплачен
                $order_payment_expense = FinancePayment::getOrderPayment($order->id, 'expense');
                if (empty($order->separate_delivery) and !empty($order->delivery_id) and $order->delivery_price > 0 and !empty($order->paid)) {

                    $delivery_method = OrderDelivery::getOne($order->delivery_id);

                    if (!empty($delivery_method->finance_purse_id)) {

                        $payment_expense = new \stdClass();
                        $payment_expense->finance_category_id = Settings::getParam('expense_finance_category_id');      # Категория платежа. Расход на доставку
                        $payment_expense->type = 0;                                                                     # Тип платежа. Расход
                        $payment_expense->manager_id = User::authUser('id');                                            # Пользователь из сессии
                        $payment_expense->purse_id = $delivery_method->finance_purse_id;                                # Выбрать соответсвующий кошелек

                        // Переводим в валюту кошелька
                        $delivery_method_purse = FinancePurse::getOne($delivery_method->finance_purse_id);
                        $payment_expense->amount = FinanceCurrency::priceConvert($order->delivery_price, intval($delivery_method_purse->currency_id), false);

                        $payment_expense_currency = FinanceCurrency::getCurrency($delivery_method_purse->currency_id);
                        $payment_expense_currency_rate = $payment_expense_currency->rate_to / $payment_expense_currency->rate_from;
                        $payment_expense->currency_rate = $payment_expense_currency_rate;

                        // Если платеж уже внемен в финансы
                        if (!empty($order_payment_expense->id)) {

                            // Обновляем. Если платеж не сверен бухгалтером
                            if (empty($order_payment_expense->verified)) {
                                $payment_expense->id = $order_payment_expense->id;
                                FinancePayment::updatePayment($payment_expense->id, $payment_expense);
                            }
                        } else {
                            $payment_expense->id = FinancePayment::addPayment($payment_expense);

                            // Добавляем контрагента "заказ"
                            $contractor = new \stdClass();
                            $contractor->payment_id = $payment_expense->id;
                            $contractor->entity_id = $order->id;
                            $contractor->entity_name = 'order';
                            FinancePaymentContractor::addContractor($contractor);
                        }
                    }
                }

                // Удалим платеж (расход)
                if (!empty($order_payment_expense->id) and empty($order_payment_expense->verified)) {
                    if (!empty($order->separate_delivery) || empty($order->delivery_id) || empty($delivery_method->finance_purse_id) || $order->delivery_price == 0 || empty($order->paid)) {
                        FinancePayment::deletePayment($order_payment_expense->id);
                    }
                }
            }


            // Отправляем письмо пользователю
            if (Request::post('notify_user') and !empty($order->email)) {

                // Send email to User
                UserNotifier::sendNotifier('Email', 'newOrderToUser', [
                    'order_id' => $order->id,
                    'to_email' => $order->email,
                    'from_name' => Settings::getParam('company_name')
                ]);
            }

            if (!empty(Request::getSession('message_success')) and Request::getSession('message_success') == 'added') {
                $this->setEvent(new OrderAddEvent($order));
            }

            // Делаем редирект на страницу с ID
            return $this->redirectToRoute('OrderAdmin', ['id' => $order->id]);
        }



        #### View
        #########
        if (!empty($id)) {

            $order = Order::getOrder($id, join: [
                'delivery_method',
                'payment_method',
                'payment_method.currency',
                'purchases',
                'purchases.product',
                'purchases.product.image',
                'purchases.product.movements',
                'labels',
                'user',
                'user.group',
                'payments',
                'payments.category',
                'payments.purse',
                'payments.contractor'
            ]);

            if (empty($order->id)) {
                return $this->redirectToRoute('OrderListAdmin');
            }

            /*
            // Выбираем товары заказа

                    // Общий вес
                    $total->purchases_weight += $purchase->variant->weight * $purchase->amount;

                    // Общая стоимость товаров. Без учета скидок
                    $total->purchases_price += $purchase->price * $purchase->amount;
                    $total->purchases_count += $purchase->amount;
                }
            }*/

            // Выбранный Менеджер
            if (!empty($order->manager_id)) {
                $order_manager = User::getUser($order->manager_id);
                $order_manager->interest_price = $order->interest_price;
                if (intval($order_manager->group->discount) > 0 and intval($order->total_price) > 0) {
                    $real_manager_discount = ($order_manager->interest_price / $order->total_price) * 100;
                    $order_manager->interest_discount =  $real_manager_discount;
                }

                Design::assign('order_manager', $order_manager);
            }

            $total =  new \stdClass();
            $total->purchases_weight =  0;
            $total->purchases_count =   0;
            $total->purchases_price =   0;
            $total->payments_price =    0;

            // Платежи
            foreach ($order->payments as $payment) {
                $sign = ($payment->type == 1) ? 1 : -1;
                $payment->amount = $sign * abs($payment->amount);

                $total->payments_price += $sign * $payment->currency_amount ?? $sign * $payment->amount;
            }

            // Выбираем предыдущий заказ
            Design::assign('prev_order', Order::getPrevOrder($order->id, $order->status));

            // Get Cart Info
            Design::assign('cart', Cart::getCartInfo(['order_id' => $order->id]));
        }

        // Определяем возможность редактировать
        $can_edit = false;
        if (empty($order->status) || (isset($order->status) and !in_array($order->status, [2, 3])) || UserPermission::checkAccess("order_edit")) {
            $can_edit = true;
        }

        // Все способы доставки
        // Потом в шаблоне .tpl выберем какой отображать
        $deliveries = OrderDelivery::getDeliveryMethods();

        // Все способы оплаты
        // Потом в шаблоне .tpl выберем какой отображать
        $payment_methods = OrderPayment::getPaymentMethods();

        Design::assign('status',             $order->status ?? 0); # Default Status
        Design::assign('order',              $order);
        Design::assign('total',              $total);
        Design::assign('labels',             OrderLabel::getLabels()); # Все Метки заказов
        Design::assign('payment_methods',    $payment_methods);
        Design::assign('deliveries',         $deliveries);
        Design::assign('can_edit',           $can_edit);

        Design::setFunctionPlugin("get_payment_module_html", OrderPayment::class, 'getPaymentModuleHtml');
        Design::setFunctionPlugin("get_delivery_module_html", OrderDelivery::class, 'getDeliveryModuleHtml');

        return $this->fetchResponse('order/order.tpl');
    }
}
