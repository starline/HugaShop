<?php

/**
 * HugaShop - Sell anything
 * 
 * @author Andi Huga
 * @version 2.0
 * 
 * This event is dispatched each time an Item
 * is added in the Cart.
 * 
 */

namespace App\Event;

use Symfony\Contracts\EventDispatcher\Event;

final class CartAddEvent extends Event
{
    private $item;

    public function __construct(array $item)
    {
        $this->item = (object) $item;
    }

    public function getItem()
    {
        return $this->item;
    }
}
