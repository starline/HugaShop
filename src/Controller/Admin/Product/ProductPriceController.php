<?php

/**
 * HugaShop - Selling anything
 *
 * @author Andri Huga
 * @version 2.4
 * 
 * ProductPriceAdmin
 *
 */

namespace App\Controller\Admin\Product;

use HugaShop\Api\Design;
use HugaShop\Api\Helper;
use HugaShop\Api\Request;
use HugaShop\Api\Settings;
use HugaShop\Api\Order\Order;
use HugaShop\Api\Product\Product;
use HugaShop\Api\Order\OrderPurchase;
use HugaShop\Api\User\UserPermission;
use App\Controller\BaseAdminController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProductPriceController extends BaseAdminController
{

    private $fillable = [
        'id' =>                 ['type' => 'int'],
        'visible' =>            ['type' => 'tinyint'],
        'disable' =>            ['type' => 'tinyint'],
        'featured' =>           ['type' => 'tinyint'],
        'sale' =>               ['type' => 'tinyint'],
        'custom' =>             ['type' => 'tinyint'],
        'awaiting' =>           ['type' => 'tinyint'],
        'variant_name' =>       ['type' => 'varchar'],
        'sku' =>                ['type' => 'varchar'],
        'price' =>              ['type' => 'decimal'],
        'cost_price' =>         ['type' => 'decimal'],
        'old_price' =>          ['type' => 'decimal'],
        'stock' =>              ['type' => 'int'],
        'weight' =>             ['type' => 'decimal'],
        'awaiting_date' =>      ['type' => 'date'],
        'product_id' =>         ['type' => 'int']
    ];


    #[Route('/admin/product/{id}/price', requirements: ['id' => '\d+'], name: 'ProductPriceAdmin')]
    public function index(int $id): Response
    {

        if (!UserPermission::checkAccess('product_price')) { # Check acces
            return $this->redirectToRoute('ProductAdmin', ['id' => $id]);
        }


        #### Update
        ###########
        if (!empty($product = Request::getDataAcces($this->fillable))) {

            // Преобразовываем дату datapiker для mysql
            if (!empty($product->awaiting_date)) {
                $product->awaiting_date =  Helper::dateConvert($product->awaiting_date . ' 12:00', 'Y-m-d');
            }

            Design::setFlashMessage('update', Product::updateProduct($product->id, $product));


            // Связанные товары
            // Удаляем все связанные товары
            Product::deleteAllRelatedProducts($product->id);
            if (!empty($rel_products_ids = Request::post('related_products', 'array'))) {

                $pos = 0;
                foreach ($rel_products_ids as $rel_id) {
                    Product::addRelatedProduct($product->id, $rel_id, $pos++);
                }
            }

            return $this->redirectToRoute('ProductPriceAdmin', ['id' => $product->id]);
        }



        #### View
        #########
        if (!empty($id)) {

            $product = Product::getProduct(intval($id));

            if (empty($product->id)) {
                return $this->redirectToRoute('ProductListAdmin');
            }

            // Связанные товары
            $related_products = Product::getRelatedProducts($product->id);

            // Заказы с этим товаром
            $filter = [
                'page' => max(1, Request::get('page', type: 'int')),
                'limit' => Request::get('page', type: 'string') == 'all' ? 'all' : Settings::getParam('products_num_admin'),
                'product_id' => $product->id
            ];

            $orders_count = Order::getOrdersCount($filter);                                      # Кол-во заказов
            $orders = Order::getOrders($filter, select: false, join: ['delivery_method', 'payment_method']);   # Выбираем заказы с этим товаром

            // Товары заказа
            foreach (OrderPurchase::getPurchases(['order_id' => array_keys($orders)], join: ['image']) as $op) {
                $orders[$op->order_id]->purchases[] = $op;
            }

            $paid_filter = ['paid' => 1, 'product_id' => $product->id]; # только оплаченые
            $orders_paid_price = Order::getOrdersPrice($paid_filter); # Выбираем общую сумму заказов

            Design::assign('pages_count', ceil($orders_count / Settings::getParam('products_num_admin')));
            Design::assign('current_page', $filter['limit'] == 'all' ? 'all' : $filter['page']);
            Design::assign('orders', $orders);
            Design::assign('orders_count', $orders_count);
            Design::assign('orders_paid_price', $orders_paid_price);

            Design::assign('related_products', $related_products);
            Design::assign('product', $product);
        }

        return $this->fetchResponse('product/product_price.tpl');
    }
}
