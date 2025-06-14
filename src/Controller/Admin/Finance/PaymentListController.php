<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.1
 *
 */

namespace App\Controller\Admin\Finance;

use HugaShop\Api\Image;
use HugaShop\Api\Design;
use HugaShop\Api\Helper;
use HugaShop\Api\Request;
use HugaShop\Api\Settings;
use HugaShop\Api\Finance\FinancePurse;
use App\Controller\BaseAdminController;
use HugaShop\Api\Finance\FinancePayment;
use HugaShop\Api\Finance\FinanceCategory;
use HugaShop\Api\Finance\FinanceCurrency;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use HugaShop\Api\Finance\FinancePaymentContractor;

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

        $filter = [];
        $filter['page'] = max(1, Request::get('page', 'int'));
        $filter['limit'] = Request::get('page', 'string') == 'all' ? 'all' : Settings::getParam('products_num_admin');

        // Поиск
        $keyword = Request::get('keyword');
        if (!empty($keyword)) {
            $filter['keyword'] = $keyword;
            Design::assign('keyword', $keyword);
        }

        // Кошелек
        if ($purse_id = Request::get('purse_id', 'integer')) {
            $filter['purse_id'] = $purse_id;
        }

        // Категория
        if ($category_id = Request::get('category_id', 'integer')) {
            $filter['category_id'] = $category_id;
        }

        // Payments type
        if ($payments_type = Request::get('payments_type', 'string')) {
            $filter['payments_type'] = $payments_type;
        }

        $payments_count = FinancePayment::countPayments($filter);
        $payments = FinancePayment::getPayments($filter);

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

        Design::assign('pages_count', ceil($payments_count / Settings::getParam('products_num_admin')));
        Design::assign('current_page', $filter['limit'] == 'all' ? 'all' : $filter['page']);

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
