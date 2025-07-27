<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.2
 *
 */

namespace App\Controller\Admin\Ajax;

use HugaShop\Services\Request;
use HugaShop\Models\Order\Order;
use HugaShop\Services\NotifierFactory;
use App\Controller\BaseAdminController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

class SmsAjax extends BaseAdminController
{

    #[Route('/admin/ajax/sms', name: 'SmsAjaxAdmin')]
    public function index()
    {

        $this->checkAdminAccess('order_edit', checkCSRF: true);

        // Выбрать данные заказа
        $order_id = Request::postInt('id');
        $order = Order::getOrder($order_id);

        $result['result']  = [];
        if (!empty($order->phone)) {


            // смс с трекномером доставки
            if (Request::post('type') == 'delivery') {
                if (!empty($order->delivery_note)) {

                    $sms_result = NotifierFactory::sendNotifier('Turbosms', [NotifierFactory::class, 'deliveryTrackNumberToUser'], [
                        'order_id' => $order->id
                    ]);

                    if (!empty($sms_result['status'])) {
                        if ($sms_result['status'] == 'Сообщения успешно отправлены') {

                            // Отмечаем, что смс доставлено. Подсчитываем сколько раз отправили SMS
                            $order->settings->delivery_sms = empty($order->settings->delivery_sms) ? 1 : $order->settings->delivery_sms++;
                            $result["result"] = Order::updateOrder($order->id, ['settings' => $order->settings], false);
                        } else {
                            $result["result"] = "error: " . $sms_result['status'];
                        }
                    } else {
                        $result["result"] = "Not delivered";
                    }
                } else {
                    $result["result"] = "Empty delivery_note";
                }
            }


            // смс с реквизитами оплаты
            elseif (Request::post('type') == 'payment') {
                if (!empty($order->payment_method_id)) {

                    // Отправляем СМС
                    $sms_result = NotifierFactory::sendNotifier('Turbosms', [NotifierFactory::class, 'paymentDetailsToUser'], [
                        'order_id' => $order->id
                    ]);

                    if (!empty($sms_result['status'])) {
                        if ($sms_result['status'] == 'Сообщения успешно отправлены') {

                            // Отмечаем, что смс доставлено. Подсчитываем сколько раз отправили SMS
                            $order->settings->payment_sms = empty($order->settings->payment_sms) ? 1 : $order->settings->payment_sms++;
                            $result["result"] = Order::updateOrder($order->id, ['settings' => $order->settings], false);
                        } else {
                            $result["result"] = "error: " . $sms_result['status'];
                        }
                    }
                } else {
                    $result["result"] = "empty payment_method_id";
                }
            }


            // Если не указан type
            else {
                $result['result'] = ['error' => 'empty type'];
            }
        } else {
            $result['result'] = ['error' => 'empty phone'];
        }

        return new JsonResponse($result);
    }
}
