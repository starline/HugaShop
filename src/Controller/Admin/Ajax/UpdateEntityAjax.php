<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.6
 *
 */

namespace App\Controller\Admin\Ajax;

use HugaShop\Api\User\User;
use HugaShop\Api\Order\Order;
use HugaShop\Api\Product\Product;
use HugaShop\Api\Request;
use HugaShop\Api\Extension;
use HugaShop\Api\Order\OrderLabel;
use HugaShop\Api\Content\ContentPage;
use HugaShop\Api\Content\ContentPost;
use HugaShop\Api\Finance\FinancePurse;
use HugaShop\Api\Order\OrderPayment;
use HugaShop\Api\Product\ProductBrand;
use HugaShop\Api\User\UserNotifier;
use HugaShop\Api\Order\OrderDelivery;
use HugaShop\Api\Content\ContentComment;
use HugaShop\Api\Finance\FinancePayment;
use HugaShop\Api\Product\ProductFeature;
use HugaShop\Api\User\UserPermission;
use HugaShop\Api\Warehouse\WarehousePlace;
use HugaShop\Api\Finance\FinanceCurrency;
use HugaShop\Api\Product\ProductCategory;
use App\Controller\BaseAdminController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

class UpdateEntityAjax extends BaseAdminController
{
    #[Route('/admin/ajax/update_entity', name: 'UpdateEntityAjaxAdmin')]
    public function update_entity()
    {

        if (!Request::checkCSRF()) {
            throw $this->createNotFoundException('Access denied'); # 404
        }

        $result = new \stdClass();

        $id = intval(Request::post('id'));
        $entity = Request::post('entity');
        $values = Request::post('values');

        switch ($entity) {
            case 'product':
                if (UserPermission::checkAccess('products')) {
                    $result = Product::updateProduct($id, $values);
                }
                break;
            case 'category':
                if (UserPermission::checkAccess('product_category')) {
                    $result = ProductCategory::updateCategory($id, $values);
                }
                break;
            case 'brands':
                if (UserPermission::checkAccess('product_brand')) {
                    $result = ProductBrand::updateBrand($id, $values);
                }
                break;
            case 'feature':
                if (UserPermission::checkAccess('product_feature')) {
                    $result = ProductFeature::updateOne($id, $values);
                }
                break;
            case 'place':
                if (UserPermission::checkAccess('warehouse_place')) {
                    $result = WarehousePlace::updateOne($id, $values);
                }
                break;
            case 'page':
                if (UserPermission::checkAccess('page')) {
                    $result = ContentPage::updateOne($id, $values);
                }
                break;
            case 'blog':
                if (UserPermission::checkAccess('blog')) {
                    $result = ContentPost::updatePost($id, $values);
                }
                break;
            case 'delivery':
                if (UserPermission::checkAccess('order_delivery')) {
                    $result = OrderDelivery::updateOne($id, $values);
                }
                break;
            case 'payment_method':
                if (UserPermission::checkAccess('order_payment')) {
                    $result = OrderPayment::updateOne($id, $values);
                }
                break;
            case 'currency':
                if (UserPermission::checkAccess('finance')) {
                    $result = FinanceCurrency::updateCurrency($id, $values);
                }
                break;
            case 'comment':
                if (UserPermission::checkAccess('comment')) {
                    $result = ContentComment::updateOne($id, $values);
                }
                break;
            case 'user':
                if (UserPermission::checkAccess('user')) {
                    $result = User::updateUser($id, $values);
                }
                break;
            case 'label':
                if (UserPermission::checkAccess('order_label')) {
                    $result = OrderLabel::updateOne($id, $values);
                }
                break;
            case 'user_notifier':
                if (UserPermission::checkAccess('user_notifier')) {
                    $result = UserNotifier::updateOne($id, $values);
                }
                break;
            case 'order':
                if (UserPermission::checkAccess('order')) {
                    $result = Order::updateOrder($id, $values);
                }
                break;
            case 'purse':
                if (UserPermission::checkAccess('finance')) {
                    $result = FinancePurse::updateOne($id, $values);
                }
                break;
            case 'payment':
                if (UserPermission::checkAccess('finance')) {
                    if (!empty($values['verified'])) {
                        $values['verified_user_id'] = User::authUser('id');
                    }
                    $result = FinancePayment::updatePayment($id, $values);
                }
            default:

                // Check extension
                $ext_list = Extension::getExtensionsList();
                foreach ($ext_list as $ext) {
                    if ($ext->module == $entity) {
                        if ($Ext = Extension::makeExtension($entity)) {
                            $Ext->updateOne($id, $values);
                        }
                        break;
                    }
                }
        }

        return new JsonResponse($result);
    }
}
