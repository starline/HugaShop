<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.2
 */

namespace HugaShop\Extensions\BookingShinomontag\Controller;

use App\Controller\BaseFrontController;
use HugaShop\Extensions\BaseExtensionTrait;
use HugaShop\Extensions\BookingShinomontag\Models\Booking;
use HugaShop\Services\Secure;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class BookingShinomontagController extends BaseFrontController
{
    use BaseExtensionTrait;

    #[Route('/booking-shinomontag', name: 'ExtBookingShinomontag', priority: 1)]
    public function booking(): Response
    {
        if (!empty($booking = Secure::getInputAcces(Booking::getFields()))) {
            $booking = Booking::createOne($booking);
            return $this->fetchExtResponse('booking_shinomontag.tpl', 'booking_sent');
        }

        return $this->fetchExtResponse('booking_shinomontag.tpl', 'booking');
    }
}
