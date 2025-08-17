<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.1
 */

namespace HugaShop\Modules\Payment;

interface PaymentInterface
{

    /**
     * Render checkout form for payment method
     *
     * @param int $order_id
     * @param string $view_type
     */
    public function checkoutForm(int $order_id, string $view_type);


    /**
     * Handle payment callback or render document
     *
     * @param string|null $order_token
     * @param string|null $form_type
     */
    public function callback(?string $order_token = null, ?string $form_type = null);
}
