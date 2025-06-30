<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 3.9
 *
 * Корзина покупок
 * Этот класс использует шаблон cart.tpl
 *
 */

namespace App\Controller\Front;

use HugaShop\Models\Cart\Cart;
use HugaShop\Models\User\User;
use HugaShop\Services\Design;
use HugaShop\Models\Helper;
use HugaShop\Models\Request;
use App\Event\OrderAddEvent;
use HugaShop\Models\User\UserCoupon;
use HugaShop\Models\Order\Order;
use HugaShop\Models\Cart\CartPurchase;
use HugaShop\Models\User\UserNotifier;
use HugaShop\Models\Order\OrderPayment;
use HugaShop\Models\Order\OrderPurchase;
use HugaShop\Models\Order\OrderDelivery;
use App\Controller\BaseFrontController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CheckoutController extends BaseFrontController
{

    private $fillout = [
        'payment_method_id' =>      ['type' => 'int'],
        'delivery_id' =>            ['type' => 'int'],
        'name' =>                   ['type' => 'string', 'trim' => true],
        'phone' =>                  ['type' => 'string', 'trim' => true],
        'email' =>                  ['type' => 'string', 'trim' => true],
        'address' =>                ['type' => 'string', 'trim' => true],
        'comment' =>                ['type' => 'string', 'trim' => true]
    ];


    #[Route('/checkout', name: 'Checkout', priority: 1)]
    public function checkout(): Response
    {

        // Выбираем товары корзины
        $cart = Cart::getCurrentCart();

        if (empty($cart->id)) {
            return $this->redirectToRoute('Cart');
        }

        // Catch checkout init
        if (empty($cart->checkout_init)) {
            Cart::updateCart($cart->id, ['checkout_init' => date('Y-m-d H:i:s')]);
        }

        $purchases = CartPurchase::getCartPurchases(['cart_id' => $cart->id, 'disabled' => 0], join: [
            'product',
            'product.image',
            'product.category'
        ]);

        $pre_order = new \stdClass();

        // Данные пользователя
        if (!empty($user = User::authUser())) {

            $last_order = Order::getOrders(['user_id' => $user->id, 'limit' => 1]);
            $last_order = reset($last_order);

            if ($last_order) {
                $pre_order->name =      $last_order->name;
                $pre_order->email =     $last_order->email;
                $pre_order->phone =     $last_order->phone;
                $pre_order->address =   $last_order->address;
            } else {
                $pre_order->name =  $user->name;
                $pre_order->email = $user->email;
            }
        }


        #### Update
        ###########
        if (Request::checkCSRF() and !empty($pre_order = Request::getDataAcces($this->fillout))) {

            // Купон
            if (!empty($coupon_code = trim(Request::post('coupon_code', 'string')))) {
                $coupon = UserCoupon::getCoupon((string)$coupon_code);
                if (empty($coupon) || !$coupon->valid) {
                    UserCoupon::applyCoupon('');
                    Design::assign('coupon_error', 'invalid');
                } else {
                    UserCoupon::applyCoupon($coupon_code);
                }
            }

            // get updated cart
            $cart = Cart::getCart(join: ['total']);


            #### Если нажали оформить заказ
            ###############################
            if (!empty(Request::post('checkout'))) {

                // Если определен пользователь, закрепляем заказ за ним
                if (!empty($user->id)) {
                    $pre_order->user_id = $user->id;
                }

                if (empty($pre_order->phone)) {
                    Design::append('form_invalid', 'phone');
                } else {

                    // Убираем пробелы в номере телефона
                    $pre_order->phone = Helper::clearPhoneNummber($pre_order->phone);

                    // Если есть телефон и нет авторизации
                    if (!empty($pre_order->phone) and empty($user->id)) {

                        // проверяем пользователя по номеру телефона
                        if ($existing_user = User::getUser(['phone' => $pre_order->phone])) {

                            $pre_order->user_id = $existing_user->id;

                            // Заполняем имя из имени пользователя, если пусто
                            if (empty($pre_order->name) and !empty($existing_user->name)) {
                                $pre_order->name = $existing_user->name;
                            }

                            // Заполняем email из имени пользователя, если пусто
                            if (empty($pre_order->email) and !empty($existing_user->email)) {
                                $pre_order->email = $existing_user->email;
                            }

                            // Проверим по номеру email
                        } elseif (!empty($pre_order->email) and $existing_user = User::getUser(['email' => $pre_order->email])) {
                            $pre_order->user_id = $existing_user->id;
                        }

                        // если такого пользователя нет, создаем его
                        else {

                            $user = new \stdClass();
                            $user->name = $pre_order->name;
                            $user->email = $pre_order->email;
                            $user->phone = $pre_order->phone;
                            $user->enabled = 1;

                            $pre_order->user_id = User::addUser($user);
                        }
                    }


                    // Добавляем заказ в базу
                    $order = Design::setFlashMessage('add', Order::addOrder($pre_order));

                    if (!empty($order->id)) {

                        Request::setSession('order_id', $order->id);

                        // Если использовали купон, увеличим количество его использований
                        if (!empty($order->coupon)) {
                            UserCoupon::updateOne($order->coupon->id, ['usages' => $cart->coupon->usages++]);
                        }

                        // Добавляем товары к заказу
                        foreach ($purchases as $i => $purchase) {
                            OrderPurchase::addPurchase([
                                'order_id' => $order->id,
                                'product_id' => $purchase->product->id,
                                'amount' => $purchase->amount,
                                'position' => $i
                            ]);
                        }

                        // Определяем стоимость доставки
                        if (!empty($order->delivery_id) and !empty($delivery = OrderDelivery::getOne($order->delivery_id))) {
                            if (($delivery->free_from > $order->total_price || $delivery->free_from == 0)) {
                                Order::updateOrder($order->id, ['delivery_price' => $delivery->price, 'separate_delivery' => $delivery->separate_payment]);
                            }
                        }

                        // Обновляем общую стоимость и прибыль, комиссию менеджера
                        Order::updateTotalPrice($order->id);


                        // Send email to User
                        UserNotifier::sendNotifier('Email', 'newOrderToUser', [
                            'order_id' => $order->id,
                            'to_email' => $order->email
                        ]);


                        // Send Notification to Admin. Telegram|Email|SMS|...
                        UserNotifier::sendNotifierToManager('newOrderToAdmin', [
                            'order_id' => $order->id
                        ]);


                        Cart::updateCart($cart->id, ['order_id' => $order->id, 'ordered' => date('Y-m-d H:i:s')]);
                        Cart::cleanCart(); # Clean Cart (session)


                        // Point for Event
                        $event = new OrderAddEvent($order);
                        $this->setEvent($event);


                        // Перенаправляем на страницу заказа
                        return $this->redirectToRoute('Order', ['id' => $order->id, 'order_url' => $order->url]);
                    }
                }
            }
        }

        // Если существуют валидные купоны, нужно вывести инпут для купона
        if (UserCoupon::countCoupons(['valid' => 1]) > 0) {
            Design::assign('coupon_request', true);
        }

        $delivery_methods   = OrderDelivery::getDeliveryMethods(['enabled' => 1, 'enabled_public' => 1]);    # Способы доставки
        $payment_methods    = OrderPayment::getPaymentMethods(['enabled' => 1, 'enabled_public' => 1]);       # Варианты оплаты

        Design::assign('delivery_methods', $delivery_methods);
        Design::assign('payment_methods', $payment_methods);
        Design::assign('noindex', true); # Закрываем от индексации
        Design::assign('purchases', $purchases);
        Design::assign('cart', $cart);

        // Сохраняем значения form на случай ошибки
        Design::assign('pre_order', $pre_order);

        return $this->fetchResponse('checkout.tpl');
    }
}
