<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.9
 *
 */

namespace HugaShop\Addons\CarouselPromo\Controller;

use HugaShop\Services\Design;
use HugaShop\Services\Secure;
use App\Controller\BaseAdminController;
use HugaShop\Addons\BaseAddonTrait;
use Symfony\Component\Routing\Attribute\Route;

final class CarouselPromoController extends BaseAdminController
{

    use BaseAddonTrait;

    /**
     * Список страниц
     */
    #[Route('/CarouselPromo', name: 'ExtCarouselPromo', priority: 20)]
    public function template()
    {

        // Обработка действий
        if (Secure::checkCSRF()) {
            //...
        }

        Design::assign('addon', $this->getAddon());

        return $this->fetchAddonResponse('index.tpl');
    }
}
