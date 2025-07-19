<?php
/**
 * HugaShop - Sell anything
 *
 * @author Andi Huga
 * @version 1.0
 */

namespace App\Event;

use Symfony\Contracts\EventDispatcher\Event;

/**
 * This event dispatched when user searches products
 */
final class ProductSearchEvent extends Event
{
    public function __construct(private string $query) {}

    public function getQuery(): string
    {
        return $this->query;
    }
}
