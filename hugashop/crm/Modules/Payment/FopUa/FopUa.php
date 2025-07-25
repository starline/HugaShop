<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.5
 *
 * Использует библиотку intl (MessageFormatter)
 * Установка на Linus: sudo apt-get install php7.4-intl
 *
 * Использует библиотку TCPDF для преобразования HTML в PDF
 * include 2D barcode class (search for installation path)
 *
 */

namespace HugaShop\Modules\Payment\FopUa;

use TCPDF;
use MessageFormatter;
use IntlDateFormatter;
use HugaShop\Services\Config;
use HugaShop\Services\Design;
use HugaShop\Services\Helper;
use HugaShop\Services\Request;
use HugaShop\Models\Order\Order;
use HugaShop\Models\Product\Product;
use HugaShop\Models\Order\OrderPayment;
use HugaShop\Models\Order\OrderDelivery;
use HugaShop\Models\Order\OrderPurchase;
use HugaShop\Models\Localization\Language;
use HugaShop\Models\Finance\FinanceCurrency;
use HugaShop\Modules\Payment\PaymentInterface;

class FopUa implements PaymentInterface
{

    public function checkoutForm(int $order_id, string $view_type)
    {

        if (!empty($order_id)) {

            $order              = Order::getOrder((int)$order_id);
            $payment_method     = OrderPayment::getOne($order->payment_method_id);
            $payment_currency   = FinanceCurrency::getCurrency(intval($payment_method->currency_id));
            $final_price        = $order->total_price;

            // Учитываем стоимость доставки
            if ($order->separate_delivery == 0 and !empty($order->delivery_price)) {
                $final_price += $order->delivery_price;
            }

            $tax_amount = FinanceCurrency::priceConvert(($final_price / ((100 - $payment_method->settings->tax) / 100)) - $final_price, $payment_method->currency_id, false);

            Design::assign('tax_amount', $tax_amount);
            Design::assign('payment_method', $payment_method);
            Design::assign('payment_currency', $payment_currency);

            // Проверим сущестование файла
            if (!empty($view_type)) {
                $file_path = Config::get('payment_dir') . $payment_method->module . '/templates/' . $payment_method->module . '_' . "$view_type.tpl";
                if (is_file($file_path)) {
                    return Design::fetch($file_path);
                }
            }

            return false;
        }
    }


    /**
     * Выводим PDF документа
     * @param ?string $order_url
     * @param ?string $form_type
     */
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

            // Set buyer name
            if (!empty($order->address)) {
                $order->name .= ', ' . $order->address;
            }
            if (!empty($order->settings->payment_name)) {
                $order->name = $order->settings->payment_name;
            }

            $payment_method     = OrderPayment::getOne($order->payment_method_id);
            $delivery_method    = OrderDelivery::getOne($order->delivery_id);


            // Выбираем товары заказа
            $purchases = [];
            if (!empty($order) and !empty($purchases = OrderPurchase::getPurchases(['order_id' => $order->id], ['image']))) {
                $doc_lang = $payment_method->settings->document_language ?? Language::getMain()->code;
                foreach ($purchases as &$purchase) {

                    if (!empty($purchase->product_id) && $doc_lang) {
                        $translation = Product::getTranslation($purchase->product_id, $doc_lang);
                        if (!empty($translation->name)) {
                            $purchase->product_name = $translation->name;
                        }
                        if (!empty($translation->variant_name)) {
                            $purchase->variant_name = $translation->variant_name;
                        }
                    }

                    // Вычисляем скидку %
                    $purchase->price = $purchase->price - ($purchase->price * ($order->discount / 100));

                    // Добавляем наценку
                    $purchase->price = round(($purchase->price / ((100 - $payment_method->settings->tax) / 100)), 2);
                }
            }

            // Если есть оплата за доставку
            if (empty($order->separate_delivery) and $order->delivery_price > 0) {
                $product = new \stdClass();
                $product->product_name = 'Пакувальний матеріал';
                $product->variant_name = ''; # . $delivery_method->name;
                $product->sku = 'sku000';
                $product->amount = 1;
                $product->price = $order->delivery_price / ((100 - $payment_method->settings->tax) / 100);
                $purchases[] = $product;
            }

            $payment_price_converted = FinanceCurrency::priceConvert($order->payment_price, $payment_method->currency_id, false);
            $payment_price_converted = explode(".", $payment_price_converted);

            $order->payment_price_spellout_int = (new MessageFormatter('uk_UA', '{n, spellout}'))->format(['n' => $payment_price_converted[0]]);
            if (!empty($payment_price_converted[1])) {
                $order->payment_price_spellout_dec  = (new MessageFormatter('uk_UA', '{n, spellout}'))->format(['n' => $payment_price_converted[1]]);
            }

            // Форматируем дату создания счета
            if ($form_type === "invoice") {
                $order->date = empty($order->settings->payment_checkdate) ? $order->date : $order->settings->payment_checkdate;
            } elseif ($form_type === "packing_list") {
                $order->date = empty($order->settings->packing_checkdate) ? $order->date : $order->settings->packing_checkdate;
            }

            // Date spellout
            $timezone = 'Europe/Kiev';
            $order->date_spellput = (new IntlDateFormatter(
                'uk_UA',
                IntlDateFormatter::FULL,
                IntlDateFormatter::FULL,
                $timezone,
                IntlDateFormatter::GREGORIAN,
                "d MMMM YYYY"
            ))->format(strtotime($order->date));

            // get currency info
            $payment_method->currency = FinanceCurrency::getCurrency($payment_method->currency_id);

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


            $file_path  = Config::get('payment_dir') . $payment_method->module . '/templates/' . $payment_method->module . '_invoice.tpl';
            $html       = Design::fetch($file_path);
            $pdf->writeHTML($html, true, false, true, false, '');


            if ($form_type === "invoice") {
                $file_name = "Order N$order->id " . Helper::dateFormat($order->date, "d.m.Y") . '.pdf';
            } elseif ($form_type === "packing_list") {
                $file_name = "Packing List N$order->id " . Helper::dateFormat($order->date, "d.m.Y") . '.pdf';
            }

            // Output the document
            $pdf->Output($file_name, 'I');
        }
    }
}
