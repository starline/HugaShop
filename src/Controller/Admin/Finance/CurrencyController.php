<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.2
 * 
 */

namespace App\Controller\Admin\Finance;

use HugaShop\Models\Design;
use HugaShop\Models\Request;
use HugaShop\Models\Order\Order;
use HugaShop\Models\Product\Product;
use HugaShop\Models\User\UserCoupon;
use HugaShop\Models\Order\OrderDelivery;
use HugaShop\Models\Order\OrderPurchase;
use App\Controller\BaseAdminController;
use HugaShop\Models\Finance\FinanceCurrency;
use Illuminate\Database\Capsule\Manager as DB;
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
            FinanceCurrency::whereNotIn('id', $currencies_ids)->delete();

            // Пересчитать курсы
            $old_currency = FinanceCurrency::getCurrency();
            $new_currency = reset($currencies);
            if ($old_currency->id != $new_currency->id) {
                $coef = $new_currency->rate_from / $new_currency->rate_to;

                if (Request::post('recalculate') == 1) {

                    // Пересчитываем цены товаров
                    Product::query()->update([
                        'price'      => DB::raw("price * $coef"),
                        'cost_price' => DB::raw("cost_price * $coef"),
                        'old_price'  => DB::raw("old_price * $coef"),
                    ]);

                    // Заказы
                    Order::query()->update([
                        'delivery_price'   => DB::raw("delivery_price * $coef"),
                        'total_price'      => DB::raw("total_price * $coef"),
                        'profit_price'     => DB::raw("profit_price * $coef"),
                        'coupon_discount'  => DB::raw("coupon_discount * $coef"),
                        'interest_price'   => DB::raw("interest_price * $coef"),
                        'payment_price'    => DB::raw("payment_price * $coef"),
                    ]);

                    // Товары заказа
                    OrderPurchase::query()->update([
                        'price'      => DB::raw("price * $coef"),
                        'cost_price' => DB::raw("cost_price * $coef"),
                    ]);

                    // Обновить value только у type = 'absolute'
                    UserCoupon::query()
                        ->where('type', 'absolute')
                        ->update([
                            'value' => DB::raw("value * $coef"),
                        ]);

                    // Обновить min_order_price у всех записей
                    UserCoupon::query()
                        ->update([
                            'min_order_price' => DB::raw("min_order_price * $coef"),
                        ]);

                    OrderDelivery::query()->update([
                        'price'     => DB::raw("price * $coef"),
                        'free_from' => DB::raw("free_from * $coef"),
                    ]);

                    // TODO Склад
                }

                // 1. Обновляем rate_from
                FinanceCurrency::query()->update([
                    'rate_from' => DB::raw("1.0 * rate_from * $new_currency->rate_to / $old_currency->rate_to"),
                ]);

                // 2. Обновляем rate_to
                FinanceCurrency::query()->update([
                    'rate_to' => DB::raw("1.0 * rate_to * $new_currency->rate_from / $old_currency->rate_from"),
                ]);

                // 3. Уравниваем rate_to и rate_from у текущей валюты
                FinanceCurrency::query()
                    ->where('id', $new_currency->id)
                    ->update([
                        'rate_to' => DB::raw("rate_from"),
                    ]);

                // 4. Обнуляемые значения — сбрасываем на 1
                FinanceCurrency::query()
                    ->where('id', $new_currency->id)
                    ->where(function ($q) {
                        $q->where('rate_to', 0)
                            ->orWhere('rate_from', 0);
                    })
                    ->update([
                        'rate_to'   => 1,
                        'rate_from' => 1,
                    ]);
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
