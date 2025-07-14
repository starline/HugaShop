<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.6
 *
 */

namespace HugaShop\Extensions\AdminTemplate\Controller;

use HugaShop\Models\Image;
use HugaShop\Services\Design;
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

        // Выбрать изображения
        Design::assign('images', Image::getList(['limit' => 3]));;

        return $this->fetchExtResponse('template.tpl');
    }
}
