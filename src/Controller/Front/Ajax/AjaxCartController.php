<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.0
 *
 */

namespace App\Controller\Front\Ajax;

use HugaShop\Api\Cart\Cart;
use HugaShop\Api\Design;
use HugaShop\Api\Request;
use App\Event\CartAddEvent;
use HugaShop\Api\Cart\CartPurchase;
use App\Controller\BaseFrontController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AjaxCartController extends BaseFrontController
{

    #[Route('/ajax/cart', name: 'AjaxCart')]
    public function cart(): Response
    {

        $amount = Request::getVar('amount', 'int') ?: 1;

        // Add product to cart
        if (!empty($variant_id = Request::getVar('variant_id', 'int'))) {
            if (CartPurchase::addCartPurchase($variant_id, $amount)) {
                $this->setEvent(new CartAddEvent(['variant_id' => $variant_id, 'amount' => $amount]));
            }
        }

        // Get updated card
        $cart = Cart::getCart(null, ['total']);
        Design::assign('cart', $cart);

        return $this->fetchResponse('parts/header.tpl', 'cart_informer');
    }
}
