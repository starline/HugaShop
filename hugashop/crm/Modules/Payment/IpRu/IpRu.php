<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 *
 * Использует библиотку intl (MessageFormatter) для преобразования цифр в сроковое написание
 * Установка на Linus: sudo apt-get install php7.4-intl
 *
 * Использует библиотку TCPDF для преобразования HTML в PDF
 * include 2D barcode class (search for installation path)
 *
 */

namespace HugaShop\Modules\Payment\IpRu;

use TCPDF;
use TCPDF2DBarcode;
use MessageFormatter;
use IntlDateFormatter;
use HugaShop\Models\Config;
use HugaShop\Models\Design;
use HugaShop\Models\Helper;
use HugaShop\Models\Request;
use HugaShop\Models\Order\Order;
use HugaShop\Models\Finance\FinanceCurrency;
use HugaShop\Models\Order\OrderPayment;
use HugaShop\Models\Order\OrderDelivery;
use HugaShop\Models\Order\OrderPurchase;

class IpRu
{
    public function checkoutForm(int $order_id, $view_type)
    {

        if (!empty($order_id)) {

            $order = Order::getOrder((int)$order_id);
            $payment_method = OrderPayment::getOne($order->payment_method_id);
            $payment_currency = FinanceCurrency::getCurrency(intval($payment_method->currency_id));

            $final_price = $order->total_price;

            // Учитываем стоимость доставки
            if ($order->separate_delivery == 0 and !empty($order->delivery_price)) {
                $final_price += $order->delivery_price;
            }

            $tax_amount = FinanceCurrency::priceConvert((intval($final_price) / ((100 - intval($payment_method->settings->tax)) / 100)) - intval($final_price), $payment_method->currency_id, false);
            $tax_inside_amount = FinanceCurrency::priceConvert(intval($final_price) * intval($payment_method->settings->tax_inside) / 100, $payment_method->currency_id, false);

            Design::assign('tax_amount', $tax_amount);
            Design::assign('tax_inside_amount', $tax_inside_amount);
            Design::assign('payment_method', $payment_method);
            Design::assign('payment_currency', $payment_currency);

            // Проверим сущестование файла
            if (!empty($view_type)) {
                $file_path = Config::get('payment_dir') . $payment_method->module . "/" . $payment_method->module . "_" . "$view_type.tpl";
                if (is_file($file_path)) {
                    return Design::fetch($file_path);
                }
            }

            return false;
        }
    }


    public function callback(?string $order_url = null, ?string $form_type = null)
    {

        // Отображаем документ по ссылке на заказ
        if (empty($order_url)) {
            $order_url = Request::get('order_url', 'string');
        }

        if (empty($form_type)) {
            $form_type = Request::get('form_type', 'string');
            if (empty($form_type)) {
                $form_type = "invoice";
            }
        }

        // Для безопасности, предоставляем доступ к квитанциям только по order_url
        if (!empty($order_url) and !empty($form_type)) {
            $order = Order::getOrder($order_url);

            // Форматируем дату создания счета
            $order->date = empty($order->settings->payment_checkdate) ? $order->date : $order->settings->payment_checkdate;

            // Set buyer name
            if (!empty($order->address)) {
                $order->name .= ', ' . $order->address;
            }
            if (!empty($order->settings->payment_name)) {
                $order->name = $order->settings->payment_name;
            }

            $payment_method = OrderPayment::getOne($order->payment_method_id);
            $delivery_method = OrderDelivery::getOne($order->delivery_id);

            if (!empty($payment_method->settings->recipient)) {
                $recipient_arr = explode(' ', $payment_method->settings->recipient);
                $recipient_short = '';
                foreach ($recipient_arr as $part) {
                    if (empty($recipient_short)) {
                        $recipient_short = $part;
                    } else {
                        $part = substr($part, 0, 2);
                        $recipient_short .= ' ' . $part . '.';
                    }
                }
                $payment_method->settings->recipient_short = $recipient_short;
            }

            // Выбираем товары заказа
            $purchases = array();
            if (!empty($order) and !empty($purchases = OrderPurchase::getPurchases(['order_id' => $order->id]))) {
                foreach ($purchases as &$purchase) {

                    // Вычисляем скидку %
                    $purchase->price = $purchase->price - ($purchase->price * ($order->discount / 100));

                    // Добавляем наценку
                    $purchase->price = ($purchase->price / ((100 - $payment_method->settings->tax) / 100));
                }
            }

            // Если есть оплата за доставку
            if (empty($order->separate_delivery) and $order->delivery_price > 0) {
                $product = new \stdClass();
                $product->product_name = 'Упаковочные материалы';
                $product->variant_name = ''; // . $delivery_method->name;
                $product->sku = 'sku000';
                $product->amount = 1;
                $product->price = $order->delivery_price / ((100 - $payment_method->settings->tax) / 100);
                $purchases[] = $product;
            }

            // Price spellout
            $payment_price_converted = FinanceCurrency::priceConvert($order->payment_price, $payment_method->currency_id, false);
            $payment_price_converted = explode(".", $payment_price_converted);

            $order->payment_price_spellout_int = (new MessageFormatter('ru_RU', '{n, spellout}'))->format(['n' => $payment_price_converted[0]]);
            if (!empty($payment_price_converted[1])) {
                $order->payment_price_spellout_dec  = (new MessageFormatter('ru_RU', '{n, spellout}'))->format(['n' => $payment_price_converted[1]]);
            }

            // Date spellout
            $order->date_spellput = (new IntlDateFormatter(
                'ru_RU',
                IntlDateFormatter::FULL,
                IntlDateFormatter::FULL,
                'Europe/Moscow',
                IntlDateFormatter::GREGORIAN,
                "d MMMM YYYY"
            ))->format(strtotime($order->date));

            // Get currency info
            $payment_method->currency = FinanceCurrency::getCurrency($payment_method->currency_id);


            // Формируем PNG QR кода для оплаты
            if ($form_type == "qrcode") {

                $QR_price = FinanceCurrency::priceConvert($order->payment_price, $payment_method->currency_id, false);
                $QR_price = str_replace(array(".", ","), "", $QR_price);

                // Убрать точки
                $QR_dara_arr = array(
                    "Name" => $payment_method->settings->business_form . ' ' . $payment_method->settings->recipient,
                    "PersonalAcc" => $payment_method->settings->account,
                    "BankNamme" => $payment_method->settings->bank,
                    "BIC" => $payment_method->settings->bik,
                    "Sum" => $QR_price,
                    "PayeeInn" => $payment_method->settings->ipn,
                    "CorrespAcc" => $payment_method->settings->ks,
                    "Purpose" => "Оплата счета № $order->id от $order->date, без НДС"
                );

                $QR_str = "G|" . urldecode(http_build_query($QR_dara_arr, "", "|"));

                // set the barcode content and type
                // Error correction 'L','M','Q','H'
                $QR_obj = new TCPDF2DBarcode($QR_str, 'QRCODE, L');

                // output the barcode as HTML object
                $QR_obj->getBarcodePng(2, 2, array(0, 0, 0));
            }

            // Выводим PDF
            else {

                Design::assign('payment_method', $payment_method);
                Design::assign('delivery_method', $delivery_method);
                Design::assign('order', $order);
                Design::assign('form_type', $form_type);
                Design::assign('purchases', $purchases);


                // Create a PDF object
                $pdf = new TCPDF('');

                $pdf->setPDFVersion('1.6');
                $pdf->SetFont('dejavusanscondensed', '', 8);

                // Set document properties
                $pdf->setPrintHeader(false);
                $pdf->setPrintFooter(false);
                $pdf->setPageOrientation('P');

                // Set font for the entire document
                $pdf->SetTextColor(0, 0, 0);

                // Set up a page
                $pdf->AddPage();
                $pdf->SetDisplayMode('real');
                $pdf->SetFontSize(9);


                $file_path = Config::get('payment_dir') . $payment_method->module . '/' . $payment_method->module . '_invoice.tpl';
                $html = Design::fetch($file_path);
                $pdf->writeHTML($html, true, false, true, false, '');


                if ($form_type == "invoice") {

                    // штамп
                    if (!empty($payment_method->settings->stamp_file)) {
                        $pdf->Image(Config::get('root_dir') . 'public/' . $payment_method->settings->stamp_file, 60, $pdf->GetY() - 34, '', '50', '', '', 'T', false, 300, '', false, false, 1, false, false, false);
                    }

                    // подпись
                    if (!empty($payment_method->settings->sign_file)) {
                        $pdf->Image(Config::get('root_dir') . 'public/' . $payment_method->settings->sign_file, 115, $pdf->GetY() + 10, '', '15', '', '', 'T', false, 300, '', false, false, 1, false, false, false);
                    }

                    $file_name = "Order N$order->id " . Helper::dateFormat($order->date, "d.m.Y") . '.pdf';
                } elseif ($form_type === "packing_list") {
                    $file_name = "Packing List N$order->id " . Helper::dateFormat($order->date, "d.m.Y") . '.pdf';
                } elseif ($form_type === "commercial_offer") {
                    $file_name = "Commercial offer N$order->id " . Helper::dateFormat($order->date, "d.m.Y") . '.pdf';
                }


                // Output the document
                $pdf->Output($file_name, 'I');
            }
        }
    }


    public function display_error($msg)
    {
        header($_SERVER['SERVER_PROTOCOL'] . ' 400 Bad Request', true, 400);
        die($msg);
    }
}
