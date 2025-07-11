<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.5
 *
 */

namespace HugaShop\Extensions\AdminTemplate\Controller;

use App\Controller\BaseAdminController;
use HugaShop\Extensions\BaseExtensionTrait;
use Symfony\Component\Routing\Attribute\Route;

final class AdminTemplateController extends BaseAdminController
{

    use BaseExtensionTrait;

    /**
     * Список странниц
     */
    #[Route('/AdminTemplate', name: 'ExtAdminTemplate', priority: 20)]
    public function template()
    {
        return $this->fetchExtResponse('template.tpl');
    }
}
