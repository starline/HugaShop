<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.8
 *
 */

namespace HugaShop\Extensions\CarouselPromo\Controller;

use HugaShop\Services\Design;
use HugaShop\Services\Secure;
use App\Controller\BaseAdminController;
use HugaShop\Extensions\BaseExtensionTrait;
use Symfony\Component\Routing\Attribute\Route;

final class CarouselPromoController extends BaseAdminController
{

    use BaseExtensionTrait;

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

        Design::assign('extension', $this->getExtension());

        return $this->fetchExtResponse('index.tpl');
    }
}
