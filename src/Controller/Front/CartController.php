<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 3.2
 *
 * Корзина покупок
 * Этот класс использует шаблон cart.tpl
 *
 */

namespace App\Controller\Front;

use HugaShop\Api\Cart\Cart;
use HugaShop\Api\Design;
use HugaShop\Api\Request;
use App\Event\CartAddEvent;
use HugaShop\Api\Cart\CartPurchase;
use App\Controller\BaseFrontController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CartController extends BaseFrontController
{

    /**
     * Cart remove items
     */
    #[Route('/cart/remove/{variant_id}', name: 'CartRemove', priority: 2)]
    public function cartRemove(int $variant_id)
    {
        CartPurchase::updatePurchase(null, $variant_id, ['disabled' => 1]); # Удаление товар из корзины
        return $this->redirectToRoute('Cart');
    }


    /**
     * Cart add items
     */
    #[Route('/cart/add/{variant_id}', name: 'CartAdd', priority: 2)]
    public function cartAdd(int $variant_id)
    {
        $amount = Request::getVar('amount', 'int') ?: 1;

        // Добавим товар в корзину
        if (CartPurchase::addCartPurchase($variant_id, $amount)) {
            $event = new CartAddEvent(['variant_id' => $variant_id, 'amount' => $amount]);
            $this->setEvent($event);
        }

        return $this->redirectToRoute('Cart');
    }


    /**
     * ViewCart
     */
    #[Route('/cart', name: 'Cart', priority: 1)]
    public function cart(): Response
    {

        // Если нам запостили amounts, обновляем их
        if ($amounts = Request::post('amounts', 'array')) {
            foreach ($amounts as $variant_id => $amount) {
                CartPurchase::updatePurchase(null, $variant_id, ['amount' => $amount]);
            }
        }

        // Get updated card
        $cart = Cart::getCart(null, ['total']);

        if (!empty($cart->id)) {

            // Выбираем товары корзины
            $purchases = CartPurchase::getCartPurchases(['cart_id' => $cart->id, 'disabled' => 0], ['image', 'product', 'variant', 'category']);
            Design::assign('purchases', $purchases);
        }

        Design::assign('cart', $cart);
        Design::assign('noindex', true);              # Закрываем от индексации
        Design::assign('is_ajax', Request::isAjax()); # Определеям тип запрос

        // Выводим корзину
        return $this->fetchResponse('cart.tpl');
    }
}
