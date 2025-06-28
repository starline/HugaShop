<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.2
 *
 */

namespace App\Controller\Admin\Finance;

use HugaShop\Models\Image;
use HugaShop\Models\Design;
use HugaShop\Models\Helper;
use HugaShop\Models\Request;
use App\Services\PaginationService;
use HugaShop\Models\Finance\FinancePurse;
use App\Controller\BaseAdminController;
use HugaShop\Models\Finance\FinancePayment;
use HugaShop\Models\Finance\FinanceCategory;
use HugaShop\Models\Finance\FinanceCurrency;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use HugaShop\Models\Finance\FinancePaymentContractor;

class PaymentListController extends BaseAdminController
{
    #[Route('/admin/finance/payments', name: 'PaymentListAdmin')]
    public function index(): Response
    {

        $this->checkAdminAccess('finance');

        $payments_types = [
            (object) ['id' => 0, 'name' => 'Расход', 'type' => 'minus'],
            (object) ['id' => 1, "name" => "Приход", 'type' => 'plus'],
            (object) ['id' => 2, "name" => "Перевод", 'type' => 'transfer']
        ];

        $filter = PaginationService::initFilter();

        // Поиск
        $keyword = Request::get('keyword');
        if (!empty($keyword)) {
            $filter['keyword'] = $keyword;
            Design::assign('keyword', $keyword);
        }

        // Кошелек
        if ($purse_id = Request::getInt('purse_id')) {
            $filter['purse_id'] = $purse_id;
        }

        // Категория
        if ($category_id = Request::getInt('category_id')) {
            $filter['category_id'] = $category_id;
        }

        // Payments type
        if ($payments_type = Request::get('payments_type', 'string')) {
            $filter['payments_type'] = $payments_type;
        }

        $payments = FinancePayment::getPayments($filter);
        $payments_count = FinancePayment::countPayments($filter);


        foreach ($payments as $p) {

            if ($p->type == 0) {
                $p->amount = -$p->amount;
            }

            // Выбираем фотоотчеты
            $images = Image::getImages($p->id, 'payment');
            $payments[$p->id]->images = $images;

            // Выбираем контрагента
            $contractor = FinancePaymentContractor::getContractor(intval($p->id));
            if (isset($contractor->entity_name)) {
                $contractor->view_name = Helper::getViewAdmin($contractor->entity_name);
            }
            $payments[$p->id]->contractor = $contractor;
        }

        // Общий баланс
        $total_amount = [];
        $total_dollars = 0;
        $currencies = FinanceCurrency::getCurrencies(); # Все валюты
        foreach ($currencies as $c) {
            $total_amount[$c->id] = $c;
            $total_amount[$c->id]->amount = FinancePurse::getTotalAmount($c->id);

            // Подсчитываем общий баланс в USD
            $total_dollars += FinanceCurrency::priceConvert($total_amount[$c->id]->amount, "USD", false, intval($c->id));
        }

        // Выбираем категории
        $categories = FinanceCategory::getCategories($payments_type);
        $categories_income = [];
        $categories_expense = [];
        foreach ($categories as $cat) {
            if ($cat->type == 1) {
                $categories_income[] = $cat;
            } else {
                $categories_expense[] = $cat;
            }
        }

        // Выбираем все кошельки
        $purses = FinancePurse::getPurses(['enabled' => 1]);

        Design::assign('pagination', PaginationService::getPagination($payments_count, $filter));

        Design::assign('payments_types', $payments_types);
        Design::assign('payments_type', $payments_type);
        Design::assign('payments', $payments);

        Design::assign('categories', $categories);
        Design::assign('categories_income', $categories_income);
        Design::assign('categories_expense', $categories_expense);
        Design::assign('category_id', $category_id);

        Design::assign('payments_count', $payments_count);
        Design::assign('total_amount', $total_amount);
        Design::assign('total_dollars', $total_dollars);
        Design::assign('purses', $purses);
        Design::assign('purse_id', $purse_id);

        // Отображение
        return $this->fetchResponse('finance/payment_list.tpl');
    }
}
