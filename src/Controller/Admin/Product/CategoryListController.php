<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.1
 *
 */

namespace App\Controller\Admin\Product;

use HugaShop\Services\Design;
use HugaShop\Models\Helper;
use HugaShop\Services\Request;
use HugaShop\Models\Product\ProductCategory;
use App\Controller\BaseAdminController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CategoryListController extends BaseAdminController
{
    #[Route('/admin/product/categories', name: 'CategoryListAdmin')]
    public function index(): Response
    {

        $this->checkAdminAccess('product_category');

        // Обработка действий
        if (Request::checkCSRF()) {

            // Действия с выбранными
            $ids = Request::post('check');
            if (is_array($ids)) {
                switch (Request::post('action')) {
                    case 'disable': {
                            foreach ($ids as $id) {
                                ProductCategory::updateCategory($id, ['visible' => 0]);
                                Design::append('service_messages_success', 'updated');
                            }
                            break;
                        }
                    case 'enable': {
                            foreach ($ids as $id) {
                                ProductCategory::updateCategory($id, ['visible' => 1]);
                                Design::append('service_messages_success', 'updated');
                            }
                            break;
                        }
                    case 'delete': {
                            ProductCategory::deleteCategory($ids);
                            Design::append('service_messages_success', 'deleted');
                            break;
                        }
                }
            }

            foreach (Helper::getPositions() as $id => $position) {
                ProductCategory::updateCategory($id, ['position' => $position]);
            }
        }

        $categories = ProductCategory::getCategoriesTree();
        Design::assign('categories', $categories);

        return $this->fetchResponse('product/category_list.tpl');
    }
}
