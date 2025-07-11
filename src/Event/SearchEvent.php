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
 * This event when Searching
 */
final class SearchEvent extends Event
{
    private $query;

    public function __construct(string $query)
    {
        $this->query = $query;
    }

    public function getQuery()
    {
        return $this->query;
    }
}
