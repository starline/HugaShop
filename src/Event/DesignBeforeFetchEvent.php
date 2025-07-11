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

final class DesignBeforeFetchEvent extends Event
{

    public function __construct(private $template) {}

    public function getTemplate()
    {
        return $this->template;
    }
}
