<?php

/**
 * 
 * @author Andi Huga
 * @version 1.0
 * 
 */

namespace App\Event;

use Symfony\Contracts\EventDispatcher\Event;

/**
 * User completed registration
 */
final class UserAddEvent extends Event
{
    private $user;

    public function __construct($user)
    {
        $this->user = $user;
    }

    public function getUser()
    {
        return $this->user;
    }
}
