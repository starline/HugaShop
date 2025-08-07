<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.0
 *
 */

namespace HugaShop\Services;

class Secure
{

    /**
     * CSRF protect
     * Get input for FORM
     */
    public static function getCSRFInput()
    {
        $token = self::setCSRF();
        return '<input type="hidden" name="csrf" value="' . $token . '">';
    }


    /**
     * CSRF protect
     * Set token
     */
    public static function setCSRF()
    {
        if (empty(Request::getSession('csrf'))) {
            Request::setSession('csrf', bin2hex(random_bytes(16))); # random token
        }
        return Request::getSession('csrf');
    }


    /**
     * CSRF protect
     * Check token
     */
    public static function checkCSRF(): bool
    {
        if (!Request::method('post') and !Request::method('get')) {
            return false;
        }

        if (empty(Request::getSession('csrf')) || empty(Request::input('csrf'))) {
            return false;
        }

        if (Request::getSession('csrf') != Request::input('csrf')) {
            return false;
        }

        return true;
    }
}
