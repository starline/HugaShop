<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.0
 */

namespace HugaShop\Extensions\BookingShinomontag\Controller;

use App\Controller\BaseFrontController;
use HugaShop\Extensions\BaseExtensionTrait;
use HugaShop\Extensions\BookingShinomontag\Models\Booking;
use HugaShop\Services\Design;
use HugaShop\Services\Request;
use HugaShop\Services\Secure;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class BookingShinomontagController extends BaseFrontController
{
    use BaseExtensionTrait;

    #[Route('/booking-shinomontag', name: 'ExtBookingShinomontag', priority: 1)]
    public function booking(): Response
    {
        if (Secure::checkCSRF()) {
            $booking = new \stdClass();
            $booking->date    = Request::post('date');
            $booking->time    = Request::post('time');
            $booking->name    = Request::post('name');
            $booking->phone   = Request::post('phone');
            $booking->comment = Request::post('comment');

            Design::assign('date', $booking->date);
            Design::assign('time', $booking->time);
            Design::assign('name', $booking->name);
            Design::assign('phone', $booking->phone);
            Design::assign('comment', $booking->comment);

            if (empty($booking->date)) {
                Design::append('form_invalid', 'date');
            } elseif (empty($booking->time)) {
                Design::append('form_invalid', 'time');
            } elseif (empty($booking->name)) {
                Design::append('form_invalid', 'name');
            } elseif (empty($booking->phone)) {
                Design::append('form_invalid', 'phone');
            } else {
                Design::assign('booking_sent', true);
                $booking = Booking::createOne($booking);
            }
        }

        return $this->fetchExtResponse('booking_shinomontag.tpl');
    }
}
