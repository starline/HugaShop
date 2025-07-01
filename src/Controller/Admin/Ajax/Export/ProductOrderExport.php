<?php

namespace App\Controller\Admin\Ajax\Export;

use HugaShop\Services\Config;
use HugaShop\Services\Helper;
use HugaShop\Services\Request;
use HugaShop\Models\Settings;
use HugaShop\Models\Order\Order;
use HugaShop\Models\Finance\FinanceCurrency;
use HugaShop\Models\Order\OrderPayment;
use HugaShop\Models\Order\OrderPurchase;
use App\Controller\BaseAdminController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

class ProductOrderExport extends BaseAdminController
{
    private $columns_names = array(
        'id' =>                     '№ Заказа',
        'date' =>                   'Дата',
        'sku' =>                    'арт.',
        'product_name' =>           'Наименование товара',
        'amount' =>                 'шт.',
        'name' =>                   'Получатель',
        'phone' =>                  'Телефон',
        'address' =>                'Город',
        'delivery_note' =>          'TTH',
        'total_price' =>            'Цена',
        'delivery_price' =>         'Цена доставки',
        'payment_price' =>          'Оплачено',
        'payment_name'  =>          'Способ оплаты',
        'interest_price' =>         '% менеджера',
    );

    private $column_delimiter = ';';
    private $filename = 'product_orders.csv';


    #[Route('/admin/ajax/product_orders/export/')]
    public function index()
    {

        $this->checkAdminAccess('export');

        // кол-во обрабатываемых заказов за раз
        $orders_count = Settings::getParam('products_num_admin');
        $export_file_path = Config::get('export_files_dir') . $this->filename;

        // Эксель кушает только 1251
        //setlocale(LC_ALL, 'ru_RU.1251');

        // Страница, которую экспортируем
        $page = Request::get('page');
        if (empty($page) || $page == 1) {
            $page = 1;

            // Если начали сначала - удалим старый файл экспорта
            if (is_writable($export_file_path)) {
                unlink($export_file_path);
            }
        }

        // Открываем файл экспорта на добавление
        $f = fopen($export_file_path, 'ab');

        // Если начали сначала - добавим в первую строку названия колонок
        if ($page == 1) {
            fputcsv($f, $this->columns_names, $this->column_delimiter);
        }

        $filter = array();
        $filter['page'] = $page;
        $filter['limit'] = $orders_count;

        $filter['paid'] = 1;
        $filter['product_id'] = Request::get('product_id');

        // Выбираем заказы
        foreach (Order::getOrders($filter) as $order) {

            // Выбираем товары заказа
            $order_purchases = OrderPurchase::getPurchases(['order_id' => $order->id]);
            foreach ($order_purchases as $index => $purchase) {
                if ($index > 0 and !empty($order)) {
                    unset($order);
                }

                $str = array();
                foreach ($this->columns_names as $var_name => $c) {
                    switch ($var_name) {

                        case 'sku':
                            $str[] = $purchase->sku;
                            break;

                        case 'product_name':
                            $product_name = $purchase->product_name;
                            if (!empty($purchase->variant_name)) {
                                $product_name .= ' - ' . $purchase->variant_name;
                            }
                            $str[] = $product_name;
                            break;

                        case 'amount':
                            $str[] = $purchase->amount;
                            break;

                        case 'payment_name':
                            if (!empty($order->payment_method_id)) {
                                $payment_method = OrderPayment::getOne($order->payment_method_id);
                                $str[] = $payment_method->name;
                            } else {
                                $str[] = "";
                            }

                            break;

                        case 'total_price':

                            // Устанавливаем формат по настройкам валюты. Без форматирования
                            if (isset($order->$var_name)) {
                                $str[] = FinanceCurrency::priceConvert($order->$var_name, null, false);
                            } else {
                                $str[] = "";
                            }
                            break;

                        case 'payment_price':

                            // Если заказ оплачен, выводим сумму платежа
                            if (!empty($order->paid)) {
                                $str[] = FinanceCurrency::priceConvert($order->$var_name, null, false);
                            } else {
                                $str[] = "";
                            }
                            break;

                        case 'delivery_price':

                            // Если стоимость доставки включена в счет
                            if (!empty($order) and empty($order->separate_delivery)) {
                                $str[] = FinanceCurrency::priceConvert($order->$var_name, null, false);
                            } else {
                                $str[] = "";
                            }
                            break;

                        case 'date':

                            // Устанавливаем формат даты
                            if (isset($order->$var_name)) {
                                $str[] = Helper::dateFormat($order->$var_name, 'd.m.Y');
                            } else {
                                $str[] = "";
                            }
                            break;

                        // Все остальные колонки
                        default:
                            if (!empty($order->$var_name)) {
                                $str[] = $order->$var_name;
                            } else {
                                $str[] = "";
                            }
                            break;
                    }
                }
                fputcsv($f, $str, $this->column_delimiter);
            }
            fputcsv($f, [], $this->column_delimiter);
        }
        fclose($f);

        $total_orders = Order::getOrdersCount($filter);

        $res =  ['end' => true, 'page' => $page, 'totalpages' => ceil($total_orders / $orders_count)];
        if ($orders_count * $page < $total_orders) {
            $res['end'] = false;
        }

        return new JsonResponse($res);
    }
}
