<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.3
 *
 */

namespace HugaShop\Services;

use HugaShop\Models\User\User;
use HugaShop\Models\Order\Order;
use HugaShop\Models\Product\Product;
use HugaShop\Models\User\UserNotifier;
use HugaShop\Models\Order\OrderPayment;
use HugaShop\Models\Content\ContentPost;
use HugaShop\Models\Order\OrderDelivery;
use HugaShop\Models\Order\OrderPurchase;
use HugaShop\Models\Content\ContentComment;
use HugaShop\Models\Finance\FinanceCurrency;

class NotifierFactory
{

    public static $message_types = [
        'user' => [
            'newOrderToUser'        => 'О новом заказе',
            'deliveryTrackNumber'   => 'Трэк номер доставка',
            'paymentInfo'           => 'Реквизиты об оплате'
        ],
        'admin' => [
            'commentToAdmin'        => 'Новый Комментарий',
            'newOrderToAdmin'       => 'Новый Заказ',
        ],
        'requared' => [
            'userPasswordRemind'
        ]
    ];


    /**
     * Send notification via Module
     *
     * @param string $module_name
     * @param string $message_type
     * @param array $message_params
     */
    public static function sendNotifier(string $module_name, string $message_type, array $message_params)
    {
        $current_module_dir = Config::get('notifier_dir') . "$module_name/";
        $current_module_tpl_path = $current_module_dir . "templates/$message_type.tpl";
        $ClassName = "HugaShop\\Modules\\Notifier\\{$module_name}\\{$module_name}";

        // Select Smarty template, module file, module name
        if (empty($module_name) || !class_exists($ClassName) || !file_exists($current_module_tpl_path)) {
            return false;
        }

        // Проверить есть ли такой method (message function)
        if (!method_exists(self::class, $message_type)) {
            return false;
        }

        // Get module settings
        $notifier_settings = UserNotifier::getNotifierSettings($module_name);
        $message_params = array_merge((array) $notifier_settings, $message_params);

        // Fetch template
        $message_content = self::$message_type($current_module_tpl_path, $message_params);

        // Run
        return $ClassName::send($message_content, $message_params);
    }


    /**
     * Send notification via Module
     *
     * @param string $module_name
     * @param string $message_content
     * @param array $message_params
     */
    public static function send(string|int $module_name, string $message_content, array $message_params = [])
    {
        if (is_numeric($module_name)) {
            $notifier = UserNotifier::getOne($module_name);

            $module_name = $notifier->module;
            $notifier_settings = $notifier->settings;
        } else {

            // Get module settings
            $notifier_settings = UserNotifier::getNotifierSettings($module_name);
        }

        if (empty($notifier_settings)) {
            return false;
        }

        $message_params = array_merge((array) $notifier_settings, $message_params);
        $ClassName = "HugaShop\\Modules\\Notifier\\{$module_name}\\{$module_name}";

        if (empty($module_name) || !class_exists($ClassName)) {
            return false;
        }

        // Run
        return $ClassName::send($message_content, $message_params);
    }


    /**
     * Get notifier types for entity
     * @param string $entity
     */
    public static function getNotifierTypes(string $entity)
    {
        return self::$message_types[$entity] ?? null;
    }


    /**
     * Выбираем модули оповещения
     * Переменные в файле settings.xml
     */
    public static function getNotifierModules()
    {
        return Helper::getModules(Config::get('notifier_dir'));
    }


    /**
     * Send Notifier manager. Select avaliable notifier Method
     * Send notification anly to Managers
     *
     * @param string $message_type
     * @param array $message_params
     */
    public static function sendNotifierToManager(string $message_type, array $message_params)
    {

        // User List to notifier
        $user_managers = User::getUsers(['manager' => 1]);
        foreach ($user_managers as $user) {
            $message_params['user'] = $user;

            // Get avaliable notifier modules for User
            $user_notifier_types = UserNotifier::getUserNotifierTypes($user->id, $message_type);
            foreach ($user_notifier_types as $notifier_id => $t) {
                $notifier = UserNotifier::getOne($notifier_id);
                self::sendNotifier($notifier->module, $message_type, $message_params);
            }
        }
    }


    /**
     * Send Comment to Admin
     *
     * @param string $template_path
     * @param array $message_params
     */
    public static function commentToAdmin(string $template_path, array &$message_params)
    {

        if (empty($message_params['comment_id']) || empty($comment = ContentComment::getOne($message_params['comment_id']))) {
            return false;
        }

        if ($comment->type == 'product') {
            $comment->product = Product::getProduct(intval($comment->entity_id));
        }
        if ($comment->type == 'blog') {
            $comment->post = ContentPost::getOne($comment->entity_id);
        }

        Design::assign([
            'comment' => $comment
        ]);

        // Image template
        $template = Design::fetch($template_path);
        $message_params['subject'] = Design::getTemplateVars('subject');

        return $template;
    }


    /**
     * Notification adout New Order for Admin
     *
     * @param string $template_path
     * @param array $message_params
     */
    public static function newOrderToAdmin(string $template_path, array &$message_params)
    {
        if (empty($message_params['order_id']) || empty($order = Order::getOrder(intval($message_params['order_id'])))) {
            return false;
        }

        $purchases = OrderPurchase::getPurchases(['order_id' => $order->id], ['image', 'product']);

        // Get Delivery and Payment methods
        $delivery_method = OrderDelivery::getOne($order->delivery_id);
        $payment_method = OrderPayment::getOne($order->payment_method_id);

        Design::assign([
            'order' => $order,
            'purchases' => $purchases,
            'payment_method' => $payment_method,
            'delivery_method' => $delivery_method
        ]);

        // Image template
        $template = Design::fetch($template_path);
        $message_params['subject'] = Design::getTemplateVars('subject');

        // Link in Button
        $message_params['url_text'] = Design::getTemplateVars('url_text');
        $message_params['url'] = Design::getTemplateVars('url');

        return $template;
    }


    /**
     * Notification adout New Order for User
     *
     * @param string $template_path
     * @param array $message_params
     */
    public static function newOrderToUser(string $template_path, array &$message_params)
    {

        if (empty($message_params['order_id']) || empty($order = Order::getOrder(intval($message_params['order_id']))) || empty($order->email)) {
            return false;
        }

        $purchases = OrderPurchase::getPurchases(['order_id' => $order->id], ['image', 'product']);

        // Get Delivery and Payment methods
        $payment_method = OrderPayment::getOne($order->payment_method_id);
        $delivery_method = OrderDelivery::getOne($order->delivery_id);


        Design::assign([
            'order' => $order,
            'purchases' => $purchases,
            'payment_method' => $payment_method,
            'delivery_method' => $delivery_method
        ]);

        // Image template
        $template = Design::fetch($template_path);
        $message_params['subject'] = Design::getTemplateVars('subject');

        return $template;
    }


    /**
     * Send code for Passwords Remind
     *
     * @param string $template_path
     * @param array $message_params
     */
    public static function userPasswordRemind(string $template_path, array &$message_params)
    {

        if (empty($message_params['user_id']) || empty($user = User::getUser($message_params['user_id']))) {
            return false;
        }

        Design::assign([
            'user' => $user,
            'code' => $message_params['code']
        ]);

        // Image template
        $template = Design::fetch($template_path);
        $message_params['subject'] = Design::getTemplateVars('subject');

        Design::clearAssign('user');
        Design::clearAssign('code');

        return $template;
    }


    /**
     * Send Delivey Track Number to User
     *
     * @param string $template_path
     * @param array $message_params
     */
    public static function deliveryTrackNumber(string $template_path, array &$message_params)
    {

        if (empty($message_params['order_id']) || empty($order = Order::getOrder(intval($message_params['order_id'])))) {
            return false;
        }

        $message_params['order'] = $order;

        Design::assign('order', $order);

        // Image template
        $template = Design::fetch($template_path);

        return $template;
    }


    /**
     * Send Payment Details to User
     *
     * @param string $template_path
     * @param array $message_params
     */
    public static function paymentDetails(string $template_path, array &$message_params)
    {

        if (empty($message_params['order_id']) || empty($order = Order::getOrder(intval($message_params['order_id'])))) {
            return false;
        }

        $message_params['order'] = $order;

        // Выбираем указаный способ оплаты
        $payment_method = OrderPayment::getOne($order->payment_method_id);
        $payment_currency = FinanceCurrency::getCurrency(intval($payment_method->currency_id));
        $payment_settings = OrderPayment::getPaymentMethodSettings($order->payment_method_id);

        Design::assign([
            'order' => $order,
            'payment_method' => $payment_method,
            'payment_currency' => $payment_currency,
            'payment_settings' => $payment_settings
        ]);

        // Image template
        $template = Design::fetch($template_path);

        return $template;
    }
}
