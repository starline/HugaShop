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
 * This event is dispatched each time an order
 * is placed in the system.
 */
final class OrderAddEvent extends Event
{
    private $order;

    public function __construct($order)
    {
        $this->order = $order;
    }

    public function getOrder()
    {
        return $this->order;
    }
}
