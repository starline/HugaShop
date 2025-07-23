<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.3
 * 
 */

namespace App\Controller\Admin\Finance;

use HugaShop\Services\Design;
use HugaShop\Services\Helper;
use HugaShop\Services\Request;
use HugaShop\Models\Finance\FinancePurse;
use HugaShop\Models\Finance\FinanceCurrency;
use App\Controller\BaseAdminController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PurseListController extends BaseAdminController
{
    #[Route('/admin/finance/purses', name: 'PurseListAdmin')]
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
                            FinancePurse::updateList($ids, ['enabled' => 0]);
                            break;
                        }
                    case 'enable': {
                            FinancePurse::updateList($ids, ['enabled' => 1]);
                            break;
                        }
                    case 'delete': {
                            FinancePurse::deletePurse($ids);
                            break;
                        }
                }
            }

            foreach (Helper::getPositions() as $id => $position) {
                FinancePurse::updateOne($id, ['position' => $position]);
            }
        }

        $purses = FinancePurse::getPurses();
        $purses_count = count($purses);

        // Общий баланс, грн (id=2)
        $total_amount = [];
        $currencies = FinanceCurrency::getCurrencies(['enabled' => 1]);
        foreach ($currencies as $c) {
            $total_amount[$c->id] = $c;
            $total_amount[$c->id]->amount = FinancePurse::getTotalAmount($c->id);
        }

        Design::assign('total_amount', $total_amount);
        Design::assign('purses_count', $purses_count);
        Design::assign('purses', $purses);

        //  Отображение
        return $this->fetchResponse('finance/purse_list.tpl');
    }
}
