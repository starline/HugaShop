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
 * This event is dispatched each time an order
 * is placed in the system.
 */
final class OrderAddEvent extends Event
{
    public function __construct(private $order) {}

    public function getOrder()
    {
        return $this->order;
    }
}
