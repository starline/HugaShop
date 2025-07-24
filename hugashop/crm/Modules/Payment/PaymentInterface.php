<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.0
 */

namespace HugaShop\Modules\Payment;

interface PaymentInterface
{
    /**
     * Render checkout form for payment method
     *
     * @param mixed $order_id
     * @param mixed $view_type
     * @return mixed
     */
    public function checkoutForm($order_id, $view_type);

    /**
     * Handle payment callback or render document
     *
     * @param string|null $order_url
     * @param string|null $form_type
     * @return mixed
     */
    public function callback(?string $order_url = null, ?string $form_type = null);
}
