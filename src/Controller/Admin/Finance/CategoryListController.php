<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.4
 * 
 */

namespace App\Controller\Admin\Finance;

use HugaShop\Services\Design;
use HugaShop\Services\Helper;
use HugaShop\Services\Request;
use HugaShop\Models\Finance\FinanceCategory;
use App\Controller\BaseAdminController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CategoryListController extends BaseAdminController
{
    #[Route('/admin/finance/categories', name: 'FinanceCategoryListAdmin')]
    public function index(): Response
    {

        $this->checkAdminAccess('finance');

        // Обработка действий
        if (Request::checkCSRF()) {

            // Действия с выбранными
            $ids = Request::post('check');
            if (is_array($ids)) {
                switch (Request::post('action')) {
                    case 'disable': {
                            FinanceCategory::updateOne($ids, ['enabled' => 0]);
                            break;
                        }
                    case 'enable': {
                            FinanceCategory::updateOne($ids, ['enabled' => 1]);
                            break;
                        }
                    case 'delete': {
                            FinanceCategory::deleteCategory($ids);
                            break;
                        }
                }
            }

            foreach (Helper::getPositions() as $id => $position) {
                FinanceCategory::updateOne($id, ['position' => $position]);
            }
        }

        $categories = FinanceCategory::getCategories();

        Design::assign('categories', $categories);
        Design::assign('categories_count', count($categories));


        //  Отображение
        return $this->fetchResponse('finance/category_list.tpl');
    }
}
