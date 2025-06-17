<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.1
 *
 */

namespace App\Controller\Admin\Order;

use HugaShop\Api\Config;
use HugaShop\Api\Design;
use HugaShop\Api\Request;
use HugaShop\Api\Finance\FinancePurse;
use HugaShop\Api\Order\OrderPayment;
use HugaShop\Api\Order\OrderDelivery;
use HugaShop\Api\Finance\FinanceCurrency;
use App\Controller\BaseAdminController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PaymentController extends BaseAdminController
{
    private $allowed_image_extentions = ['png'];

    #[Route('/admin/order/payment', name: 'OrderPaymentNewAdmin')]
    #[Route('/admin/order/payment/{id}', requirements: ['id' => '\d+'], name: 'OrderPaymentAdmin')]
    public function payment(?int $id = null): Response
    {

        $this->checkAdminAccess('order_payment');

        $payment_modules = OrderPayment::getPaymentModules();


        #### Update
        ###########
        if (!empty($payment_method = Request::getDataAcces(OrderPayment::getFields()))) {

            $payment_method_settings        = Request::post('payment_method_settings', 'array');
            $payment_method_deliveries      = Request::post('payment_method_deliveries', 'array');

            if (empty($payment_method->id)) {
                $payment_method = Design::setFlashMessage('add', OrderPayment::create($payment_method));
            } else {
                Design::setFlashMessage('update', OrderPayment::updateOne($payment_method->id, $payment_method));

                // Если есть модуль оплаты, собираем его настройки
                if (!empty($payment_method->module)) {

                    if (!empty($payment_method->module) and !empty($payment_modules[$payment_method->module])) {
                        $payment_module = $payment_modules[$payment_method->module];

                        foreach ($payment_module->settings as $module_setting) {
                            if (!empty($module_setting->type) and $module_setting->type == "file") {

                                // Upload
                                // tmp_name - file path
                                // name - file name
                                $temp_file_name = Request::files($module_setting->variable, 'tmp_name');
                                $new_file_name = "files/watermark/" . $module_setting->variable . "_" . $payment_method->module . "_" . $payment_method->id . ".png";
                                $dir_to_save = "public/" . $new_file_name;

                                if (!empty($temp_file_name) && in_array(pathinfo(Request::files($module_setting->variable, 'name'), PATHINFO_EXTENSION), $this->allowed_image_extentions)) {
                                    if (@move_uploaded_file($temp_file_name, Config::get('root_dir') . $dir_to_save)) {
                                        $payment_method_settings[$module_setting->variable] = $new_file_name;
                                    }
                                } elseif (file_exists(Config::get('root_dir') . $dir_to_save)) {
                                    $payment_method_settings[$module_setting->variable] = $new_file_name;
                                }
                            }
                        }
                    }
                    OrderPayment::updatePaymentSettings($payment_method->id, $payment_method_settings);
                }
                OrderPayment::updatePaymentDeliveries($payment_method->id, $payment_method_deliveries);
            }

            // Делаем редирект на страницу с ID
            return $this->redirectToRoute('OrderPaymentAdmin', ['id' => $payment_method->id]);
        }


        #### View
        #########
        if (!empty($id)) {
            $payment_method = OrderPayment::getOne($id, join: [
                'deliveries'
            ]);

            if (empty($payment_method->id)) {
                return $this->redirectToRoute('OrderPaymentListAdmin');
            }

            Design::assign('payment_method',             $payment_method);
        }


        Design::assign('deliveries',                 OrderDelivery::getDeliveryMethods());
        Design::assign('purses',                     FinancePurse::getPurses());
        Design::assign('payment_modules',            $payment_modules);
        Design::assign('currencies',                 FinanceCurrency::getCurrencies(['enabled' => 1]));

        return $this->fetchResponse('order/payment.tpl');
    }
}
