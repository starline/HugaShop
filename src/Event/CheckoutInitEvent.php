<?php

/**
 * HugaShop - Sell anything
 * 
 * @author Andi Huga
 * @version 1.1
 * 
 */

namespace App\Event;

use Symfony\Contracts\EventDispatcher\Event;

/**
 * Checkout initialization event
 */
final class CheckoutInitEvent extends Event
{
    public function __construct(private $cart) {}

    public function getCart()
    {
        return $this->cart;
    }
}
