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
 * This event is dispatched each time when product viewed
 * is placed in the system.
 */
final class ProductViewEvent extends Event
{

    public function __construct(private $product) {}

    public function getProduct()
    {
        return $this->product;
    }
}
