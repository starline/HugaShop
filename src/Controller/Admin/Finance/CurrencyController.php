<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.2
 * 
 */

namespace App\Controller\Admin\Finance;

use HugaShop\Api\Design;
use HugaShop\Api\Request;
use HugaShop\Api\DatabaseQuery;
use HugaShop\Api\Finance\FinanceCurrency;
use App\Controller\BaseAdminController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CurrencyController extends BaseAdminController
{
    #[Route('/admin/finance/currency', name: 'CurrencyAdmin')]
    public function index(): Response
    {

        $this->checkAdminAccess('finance');

        // Обработка действий
        if (Request::checkCSRF()) {

            foreach (Request::post('currency') as $n => $va) {
                foreach ($va as $i => $v) {
                    if (empty($currencies[$i])) {
                        $currencies[$i] = new \stdClass();
                    }
                    $currencies[$i]->$n = $v;
                }
            }

            $currencies_ids = [];
            foreach ($currencies as $currency) {

                if (!empty($currency->id)) {
                    Design::setFlashMessage('update', FinanceCurrency::updateCurrency($currency->id, $currency));
                } else {
                    $currency->id = Design::setFlashMessage('add', FinanceCurrency::addCurrency($currency));
                }

                $currencies_ids[] = $currency->id;
            }

            // Удалить непереданные валюты
            FinanceCurrency::delete()->where('id NOT IN(?@)', $currencies_ids)->get();

            // Пересчитать курсы
            $old_currency = FinanceCurrency::getCurrency();
            $new_currency = reset($currencies);
            if ($old_currency->id != $new_currency->id) {
                $coef = $new_currency->rate_from / $new_currency->rate_to;

                if (Request::post('recalculate') == 1) {

                    // Пересчитываем цены товаров
                    DatabaseQuery::query("UPDATE __product_variant SET price=price*?", $coef);
                    DatabaseQuery::query("UPDATE __product_variant SET cost_price=cost_price*?", $coef);
                    DatabaseQuery::query("UPDATE __product_variant SET old_price=old_price*?", $coef);

                    // Заказы
                    DatabaseQuery::query("UPDATE __order SET delivery_price=delivery_price*?", $coef);
                    DatabaseQuery::query("UPDATE __order SET total_price=total_price*?", $coef);
                    DatabaseQuery::query("UPDATE __order SET profit_price=profit_price*?", $coef);
                    DatabaseQuery::query("UPDATE __order SET coupon_discount=coupon_discount*?", $coef);
                    DatabaseQuery::query("UPDATE __order SET interest_price=interest_price*?", $coef);
                    DatabaseQuery::query("UPDATE __order SET payment_price=payment_price*?", $coef);
                    DatabaseQuery::query("UPDATE __order SET delivery_price=delivery_price*?", $coef);

                    // Товары заказа
                    DatabaseQuery::query("UPDATE __order_purchase SET price=price*?", $coef);
                    DatabaseQuery::query("UPDATE __order_purchase SET cost_price=cost_price*?", $coef);

                    DatabaseQuery::query("UPDATE __user_coupon SET value=value*? WHERE type='absolute'", $coef);
                    DatabaseQuery::query("UPDATE __user_coupon SET min_order_price=min_order_price*?", $coef);

                    DatabaseQuery::query("UPDATE __order_delivery SET price=price*?, free_from=free_from*?", $coef, $coef);

                    // TODO Склад
                }

                DatabaseQuery::query("UPDATE __finance_currency SET rate_from=1.0*rate_from*$new_currency->rate_to/$old_currency->rate_to");
                DatabaseQuery::query("UPDATE __finance_currency SET rate_to=1.0*rate_to*$new_currency->rate_from/$old_currency->rate_from");
                DatabaseQuery::query("UPDATE __finance_currency SET rate_to = rate_from WHERE id=?", $new_currency->id);
                DatabaseQuery::query("UPDATE __finance_currency SET rate_to = 1, rate_from = 1 WHERE (rate_to=0 OR rate_from=0) AND id=?", $new_currency->id);
            }

            // Отсортировать валюты
            asort($currencies_ids);

            $i = 0;
            foreach ($currencies_ids as $currency_id) {
                if ($i == 0) {
                    FinanceCurrency::updateCurrency($currencies_ids[$i], ['position' => $currency_id, 'rate_to' => 1, 'rate_from' => 1]);
                } else {
                    FinanceCurrency::updateCurrency($currencies_ids[$i], ['position' => $currency_id]);
                }
                $i++;
            }

            // Действия с выбранными
            $action = Request::post('action');
            $id = Request::post('action_id', 'int');

            if (!empty($action) && !empty($id)) {
                switch ($action) {
                    case 'disable': {
                            FinanceCurrency::updateCurrency($id, ['enabled' => 0]);
                            break;
                        }
                    case 'enable': {
                            FinanceCurrency::updateCurrency($id, ['enabled' => 1]);
                            break;
                        }
                    case 'show_cents': {
                            FinanceCurrency::updateCurrency($id, ['cents' => 1]);
                            break;
                        }
                    case 'hide_cents': {
                            FinanceCurrency::updateCurrency($id, ['cents' => 0]);
                            break;
                        }
                    case 'delete': {
                            FinanceCurrency::deleteCurrency($id);
                            break;
                        }
                }
            }

            return $this->redirectToRoute('CurrencyAdmin');
        }

        Design::assign('currency', FinanceCurrency::getCurrency());
        Design::assign('currencies', FinanceCurrency::getCurrencies());

        return $this->fetchResponse('finance/currency.tpl');
    }
}
