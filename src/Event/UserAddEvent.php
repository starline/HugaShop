<?php

/**
 * 
 * @author Andi Huga
 * @version 1.1
 * 
 */

namespace App\Event;

use Symfony\Contracts\EventDispatcher\Event;

/**
 * User completed registration
 */
final class UserAddEvent extends Event
{

    public function __construct(private $user) {}

    public function getUser()
    {
        return $this->user;
    }
}
