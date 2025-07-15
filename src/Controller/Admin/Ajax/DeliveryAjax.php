<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.1
 *
 */

namespace App\Controller\Admin\Ajax;

use HugaShop\Services\Request;
use App\Controller\BaseAdminController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

class DeliveryAjax extends BaseAdminController
{
    #[Route('/admin/ajax/get_delivery', name: 'DeliveryAjaxAdmin')]
    public function index()
    {

        $this->checkAdminAccess('order', checkCSRF: true);

        $result = "";
        $request_type = Request::post('request_type');

        // Выбираем информацию о доставке НовояПочта
        if ($request_type == 'checkTracking') {

            // Выбрать данные заказа
            $order_id = intval(Request::post('id'));
            $module_name = Request::post('module');

            $ClassName = "HugaShop\\Modules\\Delivery\\{$module_name}\\{$module_name}";

            if (!empty($module_name) and class_exists($ClassName)) {
                $Module = new $ClassName();
                $result = $Module->getDeliveryInfo($order_id);
            }
        }

        return new JsonResponse($result);
    }
}
