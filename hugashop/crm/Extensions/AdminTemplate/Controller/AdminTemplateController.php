<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.7
 *
 */

namespace HugaShop\Extensions\AdminTemplate\Controller;

use HugaShop\Models\Image;
use HugaShop\Services\Design;
use App\Controller\BaseAdminController;
use HugaShop\Extensions\BaseExtensionTrait;
use HugaShop\Models\Product\ProductCategory;
use Symfony\Component\Routing\Attribute\Route;

final class AdminTemplateController extends BaseAdminController
{

    use BaseExtensionTrait;

    /**
     * Список страниц
     */
    #[Route('/AdminTemplate', name: 'ExtAdminTemplate', priority: 20)]
    public function template()
    {

        Design::assign('categories',    ProductCategory::getCategoriesTree());
        Design::assign('images',        Image::getList(['limit' => 3])); # Выбрать изображения для примера
        Design::assign('extension',     $this->getExtension());

        return $this->fetchExtResponse('template.tpl');
    }
}
