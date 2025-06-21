<?php

namespace App\Controller\Admin\Ajax\Export;

use HugaShop\Models\Order\Order;
use HugaShop\Models\Config;
use HugaShop\Models\Helper;
use HugaShop\Models\Request;
use HugaShop\Models\Settings;
use HugaShop\Models\Finance\FinanceCurrency;
use App\Controller\BaseAdminController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

class OrderExport extends BaseAdminController
{
    private $columns_names = array(
        'id' =>                     '№ Заказа',
        'date' =>                   'Дата',
        'delivery_note' =>          'TTH',
        'name' =>                   'Получатель',
        'phone' =>                  'Телефон',
        'address' =>                'Город',
        'total_price' =>            'Цена',
        'delivery_price' =>         'Цена доставки',
        'payment_price' =>          'Оплачено'
    );

    private $column_delimiter = ';';
    private $filename = 'orders.csv';


    #[Route('/admin/ajax/orders/export')]
    public function index()
    {

        $this->checkAdminAccess('export');

        // кол-во обрабатываемыз заказов за раз
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

        $filter = [];
        $filter['page'] = $page;
        $filter['limit'] = $orders_count;

        $filter['status'] =  intval(Request::get('status'));
        $filter['label'] =   intval(Request::get('label'));
        $filter['keyword'] = Request::get('keyword');

        // Выбираем заказы
        foreach (Order::getOrders($filter) as $order) {
            $str = array();
            foreach ($this->columns_names as $var_name => $c) {
                switch ($var_name) {

                    case 'total_price':

                        // Устанавливаем формат по настройкам валюты. Без форматирования
                        $str[] = FinanceCurrency::priceConvert($order->$var_name, null, false);
                        break;

                    case 'payment_price':

                        // Если заказ оплачен, выводим сумму платежа
                        if (!empty($order->paid)) {
                            $str[] = FinanceCurrency::priceConvert($order->$var_name, null, false);
                        } else {
                            $str[] = '';
                        }
                        break;

                    case 'delivery_price':

                        // Если стоимость доставки включена в счет
                        if (empty($order->separate_delivery)) {
                            $str[] = FinanceCurrency::priceConvert($order->$var_name, null, false);
                        } else {
                            $str[] = '';
                        }
                        break;

                    case 'date':

                        // Устанавливаем формат даты
                        $str[] = Helper::dateFormat($order->$var_name, 'd.m.Y');
                        break;

                    // Все остальные колонки
                    default:
                        $str[] = $order->$var_name;
                        break;
                }
            }

            fputcsv($f, $str, $this->column_delimiter);
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
