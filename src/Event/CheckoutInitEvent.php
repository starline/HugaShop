<?php

/**
 * HugaShop - Sell anything
 * 
 * @author Andi Huga
 * @version 1.0
 * 
 */

namespace App\Event;

use Symfony\Contracts\EventDispatcher\Event;

/**
 * Checkout initialization event
 */
final class CheckoutInitEvent extends Event
{
    private $cart;

    public function __construct(array $cart)
    {
        $this->cart = (object) $cart;
    }

    public function getCart()
    {
        return $this->cart;
    }
}
