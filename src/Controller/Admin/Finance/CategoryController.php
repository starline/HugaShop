<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.5
 * 
 */

namespace App\Controller\Admin\Finance;

use HugaShop\Services\Design;
use HugaShop\Services\Request;
use HugaShop\Models\Finance\FinanceCategory;
use App\Controller\BaseAdminController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CategoryController extends BaseAdminController
{
    #[Route('/admin/finance/category', name: 'FinanceCategoryNewAdmin')]
    #[Route('/admin/finance/category/{id}', requirements: ['id' => '\d+'], name: 'FinanceCategoryAdmin')]
    public function index(?int $id): Response
    {

        $this->checkAdminAccess('finance');

        #### Update
        ###########
        if (!empty($category = Request::getDataAcces(FinanceCategory::getFields()))) {

            if (empty($category->id)) {
                $category = Design::setFlashMessage('add', FinanceCategory::createOne($category));
            } else {
                Design::setFlashMessage('update', FinanceCategory::updateOne($category->id, $category));
            }

            return $this->redirectToRoute('FinanceCategoryAdmin', ['id' => $category->id]);
        }


        #### View
        #########
        if (!empty($id)) {
            $category = FinanceCategory::getOne($id);

            if (empty($category->id)) {
                return $this->redirectToRoute('FinanceCategoryListAdmin');
            }
        }

        Design::assign('category', $category);

        return $this->fetchResponse('finance/category.tpl');
    }
}
