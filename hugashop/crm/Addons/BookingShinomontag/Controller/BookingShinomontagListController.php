<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.1
 */

namespace HugaShop\Addons\BookingShinomontag\Controller;

use App\Controller\BaseAdminController;
use App\Services\PaginationService;
use HugaShop\Addons\BaseAddonTrait;
use HugaShop\Addons\BookingShinomontag\Models\Booking;
use HugaShop\Services\Design;
use HugaShop\Services\Request;
use HugaShop\Services\Secure;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class BookingShinomontagListController extends BaseAdminController
{
    use BaseAddonTrait;

    #[Route('/BookingShinomontag', name: 'AddonBookingShinomontagList', priority: 20)]
    public function index(): Response
    {
        if (Secure::checkCSRF()) {
            $ids = Request::post('check');
            if (!empty($ids)) {
                if (Request::post('action') === 'delete') {
                    Booking::deleteOne($ids);
                }
            }
        }

        $filter = PaginationService::initFilter();
        $bookings       = Booking::getList($filter, order: ['id', 'desc']);
        $bookings_count = Booking::getCount($filter);

        Design::assign('pagination', PaginationService::getPagination($bookings_count, $filter));
        Design::assign('bookings', $bookings);
        Design::assign('bookings_count', $bookings_count);
        Design::assign('addon', $this->getAddon());

        return $this->fetchAddonResponse('booking_shinomontag_list.tpl');
    }
}
