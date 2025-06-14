<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.2
 *
 */

namespace HugaShop\Extensions\SmsSender;

use HugaShop\Api\Design;
use HugaShop\Api\Helper;
use HugaShop\Api\Request;
use HugaShop\Api\Settings;
use HugaShop\Api\Order\Order;
use HugaShop\Api\Product\Product;
use HugaShop\Api\User\UserMailing;
use HugaShop\Api\User\UserNotifier;
use HugaShop\Extensions\BaseExtension;
use HugaShop\Api\User\UserMailTemplate;
use HugaShop\Extensions\SmsSender\Model\SmsSenderMail;
use HugaShop\Extensions\SmsSender\Model\SmsSender as ModelSmsSender;

final class SmsSender extends BaseExtension
{

    /**
     * Список рассылок
     */
    public function index()
    {

        // Обработка действий
        if (Request::checkCSRF()) {

            // Действия с выбранными
            $ids = Request::post('check');
            if (is_array($ids)) {
                switch (Request::post('action')) {
                    case 'delete': {
                            ModelSmsSender::deleteOne($ids);
                            break;
                        }
                }
            }
        }

        // Список рассылок
        $mailings = ModelSmsSender::getList();
        Design::assign('mailings', $mailings);

        return $this->getTemplatePath('templates/index.tpl');
    }


    /**
     * Рассылка
     * @param ?int $mailing_id
     */
    public function mailing(?int $mailing_id = null)
    {

        $mailing_list = [];


        #### Update
        ###########
        if (!empty($mailing = Request::getDataAcces(ModelSmsSender::$table_fields))) {

            $product_list = preg_split('/\r\n|\r|\n/', Request::post('product_list', 'string')); # Формирум мaссив из строк
            $mailing->product_list = serialize($product_list);

            $category_list = preg_split("/\r\n|\r|\n/", Request::post('category_list', 'string')); # Формирум мaссив из строк
            $mailing->category_list = serialize($category_list);

            // Выбрать все товары категорий
            $category_product_list = Product::getProducts(['category_id' => $category_list]);
            $category_product_list = array_keys($category_product_list);

            $user_prod_list = [];
            $product_list = array_merge($product_list, $category_product_list);

            if (!empty($product_list)) {
                foreach ($product_list as $product_id) {
                    if (empty($product_id)) {
                        continue;
                    }

                    // Выбираем заказы с этим товаром
                    $orders_filter = ['product_id' => $product_id];
                    $orders = Order::getOrders($orders_filter);
                    foreach ($orders as $order) {
                        $user_prod_list[] = $order->phone;
                    }
                }
            }

            $user_old_list = preg_split('/\r\n|\r|\n/', Request::post('user_list')); # Формирум мaссив из строк

            // Объеденить
            $user_list = array_merge($user_prod_list, $user_old_list);
            $user_list = array_unique($user_list, SORT_STRING);
            $mailing->user_list = serialize($user_list);

            if (empty($mailing->id)) {
                $mailing->token = Helper::makeToken(uniqid(), 5);
                $mailing = Design::setFlashMessage('add', ModelSmsSender::create($mailing));
            } else {
                Design::setFlashMessage('update', ModelSmsSender::updateOne($mailing->id, $mailing));
            }

            // Делаем редирект на страницу с ID
            Request::makeRedirect("/admin/extension/SmsSender/mailing/$mailing->id");
        }


        #### View
        #########
        if (!empty($mailing_id)) {

            $mailing = ModelSmsSender::getOne($mailing_id);

            if (empty($mailing->id)) {
                Request::makeRedirect("/admin/extension/SmsSender");
            }

            if (!empty($mailing->product_list)) {
                $mailing->product_list = unserialize($mailing->product_list);
            }

            if (!empty($mailing->category_list)) {
                $mailing->category_list = unserialize($mailing->category_list);
            }

            if (!empty($mailing->user_list)) {
                $mailing->user_list = unserialize($mailing->user_list);
            }

            // Отправка в очередь
            if (!empty(Request::post('sending'))) {
                if (!empty($mailing->notifier_id)) {
                    foreach ($mailing->user_list as $phone) {
                        if (empty($phone)) {
                            continue;
                        }

                        $mail = new \stdClass();
                        $mail->notifier_id = $mailing->notifier_id;
                        $mail->contact = $phone;
                        $mail->template_id = $mailing->template_id;
                        $mail->settings = ['landing_url' => $mailing->landing_url];

                        $mail->id = UserMailing::addMailing($mail, false);
                        if (!empty($mail->id)) {
                            SmsSenderMail::create(['sender_id' => $mailing->id, 'mail_id' => $mail->id]);
                        }
                    }
                }
            }

            $filter = [];
            $filter['page'] = max(1, Request::get('page', 'int'));
            $filter['limit'] = Request::get('page', 'string') == 'all' ? 'all' : Settings::getParam('products_num_admin');

            $mailing_list = SmsSenderMail::getMailingList($mailing->id, $filter);
            $mailing_count = SmsSenderMail::getMailingCount($mailing->id, $filter);

            Design::assign('mailing_list', $mailing_list);
            Design::assign('mailing_count', $mailing_count);
            Design::assign('pages_count', ceil($mailing_count / Settings::getParam('products_num_admin')));
            Design::assign('current_page', $filter['page']);
        }

        if (!empty($mailing->template_id)) {
            $mailing->template = UserMailTemplate::getOne($mailing->template_id);
        }

        $notifiers = UserNotifier::getList(filter: ['enabled' => 1, 'type' => 'sms'], order: 'position');

        Design::assign('mailing', $mailing);
        Design::assign('notifiers', $notifiers);

        // Проверим сущестование файла
        return $this->getTemplatePath('templates/mailing.tpl');
    }


    /**
     * Webhook module
     */
    public function webhook(array $params = [])
    {
        return false;
    }
}
