<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @author Artem Sabelnikov
 * @version 2.5
 *
 * При переводе выбираем два связаных платежа
 * 
 */

namespace App\Controller\Admin\Finance;

use HugaShop\Services\Design;
use HugaShop\Services\Helper;
use App\Services\ImageService;
use HugaShop\Models\User\User;
use HugaShop\Services\Request;
use App\Controller\BaseAdminController;
use HugaShop\Models\Finance\FinancePurse;
use HugaShop\Models\Finance\FinancePayment;
use HugaShop\Models\Finance\FinanceCategory;
use HugaShop\Models\Finance\FinanceCurrency;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use HugaShop\Models\Finance\FinancePaymentContractor;

class PaymentController extends BaseAdminController
{
    // Типы контрагента
    private $contractor_types = array(
        ['name' => 'Заказ',                 'entity_name' => 'order',           'search' => 'search/order'],
        ['name' => 'Пользователь',          'entity_name' => 'user',            'search' => 'search/user'],
        ['name' => 'Складское перемещение', 'entity_name' => 'wh_movement',     'search' => 'search/movement']
    );


    #[Route('/admin/finance/payment', name: 'PaymentNewAdmin')]
    #[Route('/admin/finance/payment/{id}', requirements: ['id' => '\d+'], name: 'PaymentAdmin')]
    public function index(?int $id = null): Response
    {

        $this->checkAdminAccess('finance');

        $rel_payment = new \stdClass();
        $contractor = new \stdClass();
        $current_currency = null;
        $cur_type = Request::get('cur_type', 'string');


        #### Update
        ###########
        if (!empty($payment = Request::getInputCheckEditAccess(FinancePayment::class, $id))) {

            // Если платеж верефицирован, определяем пользователя
            if (!empty($payment->verified)) {
                $payment->verified_user_id  = User::authUser('id');
            }

            // Если перевод, создаем второй платеж
            if (!empty($purse_to_id = Request::postInt('purse_to_id'))) {
                $rel_payment = clone $payment;
                $rel_payment->purse_id = $purse_to_id;
            }


            // Создаем платеж
            /////////////////
            if (empty($payment->id)) {

                // Менеджер создавший
                $payment->manager_id  = User::authUser('id');

                // Тип платежа по-умолчанию "Расход", при создании перевода
                $payment->type = isset($payment->type) ? $payment->type : 0;
                $payment->id = Design::setFlashMessage('add', FinancePayment::addPayment($payment));

                if (!empty($payment->id)) {

                    // Если перевод, создаем второй платеж
                    if (!empty($rel_payment->purse_id)) {

                        $rel_payment->related_payment_id = $payment->id;

                        // Пересчитываем по курсу
                        $rel_payment->amount = $payment->currency_amount;
                        $rel_payment->currency_rate = 1 / $payment->currency_rate;
                        $rel_payment->type = ($payment->type == 1) ? 0 : 1;
                        $rel_payment->manager_id = $payment->manager_id;
                        $rel_payment->id = FinancePayment::addPayment($rel_payment);

                        if (!empty($rel_payment->id)) {
                            FinancePayment::updatePayment($payment->id, ["related_payment_id" => $rel_payment->id]);
                        }
                    }
                }
            }


            // Обновляем платеж
            ///////////////////
            else {

                Design::setFlashMessage('update', FinancePayment::updatePayment($payment->id, $payment));

                if (!empty($payment = FinancePayment::getPayment($payment->id))) {

                    // Если перевод
                    if (!empty($rel_payment->purse_id)) {
                        $rel_payment->related_payment_id = $payment->id;
                        $rel_payment->id = $payment->related_payment_id;

                        // пересчитываем по курсу
                        $rel_payment->amount = $payment->currency_amount;
                        $rel_payment->currency_rate = 1 / $payment->currency_rate;
                        $rel_payment->type = ($payment->type == 1) ? 0 : 1;

                        FinancePayment::updatePayment($rel_payment->id, $rel_payment);
                    }
                }
            }

            // Обработка связи с сущностью
            if (!is_null(Request::post('entity_name', 'string')) and !is_null(Request::postInt('entity_id'))) {
                $contractor->payment_id = $payment->id;
                $contractor->entity_id = Request::post('entity_id');
                $contractor->entity_name = Request::post('entity_name');
                FinancePaymentContractor::addContractor($contractor);
            } else {
                FinancePaymentContractor::deleteContractor($payment->id);
            }

            ImageService::catchImages($payment->id, 'payment', 'images');

            return $this->redirectToRoute('PaymentAdmin', ['id' => $payment->id]);
        }


        #### View
        #########
        if (!empty($id)) {

            $payment = FinancePayment::getOne($id, join: ['category', 'purse', 'purse.currency', 'images']);

            if (empty($payment->id)) {
                return $this->redirectToRoute('PaymentListAdmin');
            }

            if (!empty($payment->related_payment_id)) {
                $rel_payment = FinancePayment::getPayment(intval($payment->related_payment_id));
                $cur_type = 2;
            }

            $contractor = FinancePaymentContractor::getContractor(intval($payment->id));
        }


        #### View create
        ################
        else {

            // Определяем предопределенного контрагента
            if (!empty(Request::get('contractor_entity_name', 'string')) and !empty(Request::getInt('contractor_entity_id'))) {
                $contractor->entity_name = Request::get('contractor_entity_name', 'string');
                $contractor->entity_id = Request::getInt('contractor_entity_id');
                $contractor = FinancePaymentContractor::setContractorName($contractor);
            }
        }


        // Устанавливаем module
        if (isset($contractor->entity_name)) {
            $contractor->view_name = Helper::getViewAdmin($contractor->entity_name);
        }

        if (!empty($payment->manager_id)) {
            $payment->manager = User::getUser($payment->manager_id);
        }

        if (!empty($payment->verified_user_id)) {
            $payment->verified_user = User::getUser($payment->verified_user_id);
        }

        $purses =       FinancePurse::getPurses();             # Выбрать кошелек
        $categories =   FinanceCategory::getCategories();      # Выбрать категорию
        $currencies =   FinanceCurrency::getCurrencies();      # Выбираем валюты

        $to_currency = FinanceCurrency::getMainCurrency();
        if (isset($payment->id)) {
            if (isset($cur_type) and $cur_type == 2 and isset($rel_payment)) {
                $current_purse      = FinancePurse::getOne($payment->purse_id);
                $current_currency   = FinanceCurrency::getCurrency((int)$current_purse->currency_id);
                $to_purse           = FinancePurse::getOne($rel_payment->purse_id);
                $to_currency        = FinanceCurrency::getCurrency((int)$to_purse->currency_id);
            } else {
                $current_purse      = FinancePurse::getOne($payment->purse_id);
                $current_currency   = FinanceCurrency::getCurrency((int)$current_purse->currency_id);
            }
        } else {
            $current_currency = FinanceCurrency::getCurrency((int)$purses[0]->currency_id);
        }

        Design::assign('payment', $payment);
        Design::assign('rel_payment', $rel_payment);
        Design::assign('purses', $purses);

        Design::assign('current_currency', $current_currency);
        Design::assign('to_currency', $to_currency);

        Design::assign('categories', $categories);
        Design::assign('currencies', $currencies);
        Design::assign('contractor', $contractor);
        Design::assign('contractor_types', $this->contractor_types);
        Design::assign('cur_type', $cur_type);

        // Отображение
        return $this->fetchResponse('finance/payment.tpl');
    }
}
