<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.7
 *
 */

namespace HugaShop\Extensions\CarouselPromo\Controller;

use HugaShop\Services\Request;
use App\Controller\BaseAdminController;
use HugaShop\Extensions\BaseExtensionTrait;
use Symfony\Component\Routing\Attribute\Route;

final class CarouselPromoController extends BaseAdminController
{

    use BaseExtensionTrait;

    /**
     * Список странниц
     */
    #[Route('/CarouselPromo', name: 'ExtCarouselPromo', priority: 20)]
    public function template()
    {

        // Обработка действий
        if (Request::checkCSRF()) {
            //...
        }

        return $this->fetchExtResponse('index.tpl');
    }
}
