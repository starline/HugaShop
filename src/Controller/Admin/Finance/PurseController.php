<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.1
 * 
 */

namespace App\Controller\Admin\Finance;

use HugaShop\Services\Design;
use HugaShop\Models\Request;
use HugaShop\Models\Finance\FinancePurse;
use HugaShop\Models\Finance\FinanceCurrency;
use App\Controller\BaseAdminController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PurseController extends BaseAdminController
{
    #[Route('/admin/finance/purse', name: 'PurseNewAdmin')]
    #[Route('/admin/finance/purse/{id}', requirements: ['id' => '\d+'], name: 'PurseAdmin')]
    public function index(?int $id): Response
    {

        $this->checkAdminAccess('finance');

        #### Update
        ###########
        if (!empty($purse = Request::getDataAcces(FinancePurse::getFields()))) {

            if (empty($purse->id)) {
                $purse = Design::setFlashMessage('add', FinancePurse::createOne($purse));
            } else {
                Design::setFlashMessage('update', FinancePurse::updateOne($purse->id, $purse));
            }

            return $this->redirectToRoute('PurseAdmin', ['id' => $purse->id]);
        }


        #### View
        #########
        if (!empty($id)) {

            $purse = FinancePurse::getOne($id);

            if (empty($purse->id)) {
                return $this->redirectToRoute('PurseListAdmin');
            }

            // Делаем сверку приходов и расходов кошелька
            $check_purse_amount = FinancePurse::checkAmount($purse->id);
            Design::assign('check_purse_amount', $check_purse_amount);
        }


        #### View Create
        ################
        else {
            $purse = new \stdClass();
            $purse->amount = "0.00";
        }

        //  Выбрать валюту
        $currencies = FinanceCurrency::getCurrencies(['enabled' => 1]);

        Design::assign('currencies', $currencies);
        Design::assign('purse', $purse);

        return $this->fetchResponse('finance/purse.tpl');
    }
}
