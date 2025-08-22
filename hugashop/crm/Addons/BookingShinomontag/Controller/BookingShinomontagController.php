<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.3
 */

namespace HugaShop\Addons\BookingShinomontag\Controller;

use App\Controller\BaseFrontController;
use HugaShop\Addons\BaseAddonTrait;
use HugaShop\Addons\BookingShinomontag\Models\Booking;
use HugaShop\Services\Secure;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class BookingShinomontagController extends BaseFrontController
{
    use BaseAddonTrait;

    #[Route('/booking-shinomontag', name: 'ExtBookingShinomontag', priority: 1)]
    public function booking(): Response
    {
        if (!empty($booking = Secure::getInputAcces(Booking::getFields()))) {
            $booking = Booking::createOne($booking);
            return $this->fetchAddonResponse('booking_shinomontag.tpl', 'booking_sent');
        }

        return $this->fetchAddonResponse('booking_shinomontag.tpl', 'booking');
    }
}
