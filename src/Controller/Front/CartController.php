<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 3.5
 *
 * Корзина покупок
 * Этот класс использует шаблон cart.tpl
 *
 */

namespace App\Controller\Front;

use HugaShop\Models\Cart\Cart;
use HugaShop\Services\Design;
use HugaShop\Services\Request;
use App\Event\CartAddEvent;
use HugaShop\Models\Cart\CartPurchase;
use App\Controller\BaseFrontController;
use HugaShop\Models\Product\Product;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CartController extends BaseFrontController
{

    /**
     * Cart remove items
     */
    #[Route('/cart/remove/{product_id}', name: 'CartRemove', priority: 2)]
    public function cartRemove(int $product_id)
    {
        CartPurchase::updatePurchase(null, $product_id, ['disabled' => 1]); # Удаление товар из корзины
        return $this->redirectToRoute('Cart');
    }


    /**
     * Cart add items
     */
    #[Route('/cart/add', name: 'CartAdd', priority: 2)]
    public function cartAdd()
    {
        $product_id     = Request::input('product_id', 'int') ?: null;
        $sku            = Request::input('sku', 'string') ?: null;
        $amount         = Request::input('amount', 'int') ?: 1;

        // SKU
        if ($sku and !$product_id) {
            $product_id = Product::getOne(['sku' => $sku])?->id;
        }

        // Добавим товар в корзину
        if (CartPurchase::addCartPurchase($product_id, $amount)) {
            $event = new CartAddEvent(['product_id' => $product_id, 'amount' => $amount]);
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

        if (Request::has('informer')) {
            return $this->cartInformer();
        }

        $amounts = Request::post('amounts', 'array');

        // Если нам запостили amounts, обновляем их
        if ($amounts) {
            foreach ($amounts as $product_id => $amount) {
                CartPurchase::updatePurchase(null, $product_id, ['amount' => $amount]);
            }
        }

        // Get updated card
        $cart = Cart::getCart(join: ['total']);

        if (!empty($cart->id)) {

            // Выбираем товары корзины
            $purchases = CartPurchase::getCartPurchases(['cart_id' => $cart->id, 'disabled' => 0], join: [
                'product',
                'product.image',
                'product.category'
            ]);
            Design::assign('purchases', $purchases);
        }

        Design::assign('cart', $cart);
        Design::assign('noindex', true);              # Закрываем от индексации
        Design::assign('is_ajax', Request::isAjax()); # Определеям тип запрос

        // Выводим корзину
        return $this->fetchResponse('cart.tpl');
    }


    /**
     * Get cart Informer
     */
    public function cartInformer()
    {

        $amount     = Request::input('amount', 'int') ?: 1;
        $product_id = Request::input('product_id', 'int') ?: null;

        // Add product to cart
        if (!empty($product_id)) {
            if (CartPurchase::addCartPurchase($product_id, $amount)) {
                $this->setEvent(new CartAddEvent(['product_id' => $product_id, 'amount' => $amount]));
            }
        }

        // Get updated card
        $cart = Cart::getCart(join: ['total']);
        Design::assign('cart', $cart);

        return $this->fetchResponse('parts/header.tpl', 'cart_informer');
    }
}
