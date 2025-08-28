<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.4
 *
 * Для оператора NovaPoshta.ua
 *
 * Документация Новой Почты
 * https://devcenter.novaposhta.ua/
 *
 * Use Guzzle lib
 * @link https://github.com/guzzle/guzzle
 *
 */

namespace HugaShop\Modules\Delivery\NovaPoshta;

use GuzzleHttp\Client as GuzzleClient;
use HugaShop\Services\Config;
use HugaShop\Services\Design;
use HugaShop\Services\Helper;
use HugaShop\Models\Order\Order;
use HugaShop\Models\Order\OrderDelivery;

class NovaPoshta
{
    // Ключ к API
    private $api_key = '';
    private $api_url = 'https://api.novaposhta.ua/v2.0/json/';


    /**
     * Выводим форму
     * @param int $order_id
     * @param string $view_type
     */
    public function checkoutForm(int $order_id, string $view_type)
    {

        $order = Order::getOrder((int)$order_id);
        $delivery_method = OrderDelivery::getOne($order->delivery_id);

        // Проверим сущестование файла
        if (!empty($view_type)) {
            $file_path = Config::get('delivery_dir') . $delivery_method->module . "/" . $delivery_method->module . "_" . "$view_type.tpl";
            if (is_file($file_path)) {
                return Design::fetch($file_path);
            }
        }

        return false;
    }


    /**
     * Выбираем информацию о ТТН
     * @param int $order_id
     */
    public function getDeliveryInfo(int $order_id)
    {

        $result = "";

        // Выбрать данные заказа
        $order = Order::getOrder($order_id);

        if (empty($order->delivery_id)) {
            return false;
        }

        // Get settings
        $delivery_method = OrderDelivery::getOne($order->delivery_id);
        if (!empty($delivery_method->settings->api_key)) {
            $this->api_key = $delivery_method->settings->api_key;
        }

        if (!empty($order->delivery_note)) {

            $phone = !empty($order->phone) ? $order->phone : "";

            // Выбрать данные по ТТН
            $NPresult = $this->checkTracking($order->delivery_note, $phone);

            if (isset($NPresult['success']) and $NPresult['success'] == "true") {
                $data = $NPresult["data"][0];

                if (isset($data['CitySender'])) {
                    $result =
                        '<b>' . $data['CitySender'] . '</b> - ' . $data['WarehouseSender'] . ' - (' . $data['DateCreated'] . ')<br>' .
                        '<b>' . $data['CityRecipient'] . '</b> - ' . $data['WarehouseRecipient'] . ' - (' . $data['ActualDeliveryDate'] . ')<br>' .
                        'Предварительная дата доставки - ' . $data['ScheduledDeliveryDate'] . '<br>' .
                        ((!empty($data['DateFirstDayStorage'])) ? 'Платное хранение с ' . $data['DateFirstDayStorage'] . '<br>' : '') .
                        "<b>" . ((stripos($data['Status'], "Відправлення отримано") !== false) ? "Відправлення <b class='color_green'>отримано</b>" : $data['Status']) . '</b> - ' . $data['RecipientDateTime'] . '<br>' .
                        'Кол-во мест - ' . $data['SeatsAmount'] . '<br>' .
                        'Вес - ' . $data['VolumeWeight'] . 'кг <br>' .
                        'Итоговая стоимость доставки - ' . $data['DocumentCost'] . ' грн <br>' .
                        'Оценочная стоимость - ' . $data['AnnouncedPrice'] . ' грн <br>' .
                        'Отправка от ' . $data['CounterpartySenderDescription'] . '<br>' .
                        'Отправитель - ' . $data['SenderFullNameEW'] . '<br>' .
                        'Получатель - ' . $data['RecipientFullName'] . '<br>' .
                        'Оплачено - <b>' . $data['AfterpaymentOnGoodsCost'] . ' грн </b>' .
                        ((isset($data['ExpressWaybillPaymentStatus']) and $data['ExpressWaybillPaymentStatus'] == "Payed") ? '- <b class="color_green">Оплачено</b>' : '- Не оплачено') . '<br>';

                    if (isset($NPresult['warnings'][0])) {
                        foreach ($NPresult['warnings'][0] as $warning) {
                            $result .= $warning . '<br>';
                        }
                    }
                } else {
                    $result = $data['Status'];
                }
            } else {
                $result = "Возникла непредвиденная ошибка";
            }

            // Выводим весь масив с данными
            //$result = $NPresult;

            // Добавляем дату //22-09-2021
            $result = $result . "</br> Обновлено - " . Helper::dateFormat($order->date, "d-m-Y H:i:s");

            // Записываем информацию в базу
            Order::updateOrder($order->id, ['delivery_info' => $result], false);

            return $result;
        }
    }


    /**
     * Запрос к api НоваяПочта
     * @param string $track
     * @param string $phone
     */
    public function checkTracking(string $track, string $phone)
    {
        $track = trim($track); # Очистить пробелы вконце и вначале
        $property["Documents"][] = [
            "DocumentNumber" => strval($track),
            "Phone" => $phone
        ];
        return $this->getResponse("TrackingDocument", "getStatusDocuments", $property);
    }


    /**
     * Response to API
     * @param string $model
     * @param string $method
     * @param string $property
     */
    private function getResponse($model, $method, $property)
    {
        $params = [
            "apiKey"            => $this->api_key,
            "modelName"         => $model,
            "calledMethod"      => $method,
            "methodProperties"  => $property
        ];

        // Send an asynchronous request
        $client = new GuzzleClient();
        $response = $client->request('PUT', $this->api_url, [
            'json' =>  $params
        ]);

        return json_decode($response->getBody(), true);
    }
}
