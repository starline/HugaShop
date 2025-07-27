<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.4
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
            'newOrderToUser'              => ['name' => 'О новом заказе'],
            'deliveryTrackNumberToUser'   => ['name' => 'Трэк номер доставка'],
            'paymentDetailsToUser'        => ['name' => 'Реквизиты об оплате']
        ],
        'admin' => [
            'commentToAdmin'              => ['name' => 'Новый Комментарий'],
            'newOrderToAdmin'             => ['name' => 'Новый Заказ'],
        ],
        'system' => [
            'passwordRemindToUser'        => ['name' => 'Восстановление пароля']
        ]
    ];


    /**
     * Send notification template via Module
     *
     * @param string $module_name
     * @param string $message_type
     * @param array $message_data
     */
    public static function sendNotifier(string $module_name, string $message_type, array $message_data)
    {
        $current_module_dir         = Config::get('notifier_dir') . "$module_name/";
        $current_module_tpl_path    = $current_module_dir . "templates/$message_type.tpl";
        $ClassName                  = "HugaShop\\Modules\\Notifier\\{$module_name}\\{$module_name}";

        // Select Smarty template, module file, module name
        if (empty($module_name) || !class_exists($ClassName) || !file_exists($current_module_tpl_path)) {
            return false;
        }

        // Проверить есть ли такой method (message function)
        if (!method_exists(self::class, $message_type)) {
            return false;
        }

        // Get module settings
        $notifier_settings  = UserNotifier::getNotifierSettings($module_name);
        $message_data     = array_merge((array) $notifier_settings, $message_data);

        // Fetch template
        $message_content = self::$message_type($current_module_tpl_path, $message_data);

        // Run
        return $ClassName::send($message_content, $message_data);
    }


    /**
     * Send notification message via Module
     *
     * @param string $module_name
     * @param string $message_content
     * @param array $message_data
     */
    public static function send(string|int $module_name, string $message_content, array $message_data = [])
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

        $message_data = array_merge((array) $notifier_settings, $message_data);
        $ClassName = "HugaShop\\Modules\\Notifier\\{$module_name}\\{$module_name}";

        if (empty($module_name) || !class_exists($ClassName)) {
            return false;
        }

        // Run
        return $ClassName::send($message_content, $message_data);
    }


    /**
     * Send Notifier manager. Select avaliable notifier Method
     * Send notification only to Managers
     *
     * @param string $message_type
     * @param array $message_data
     */
    public static function sendToManagers(string $message_type, array $message_data)
    {

        // User List to notifier
        $user_managers = User::getUsers(['manager' => 1]);
        foreach ($user_managers as $user) {
            $message_data['user'] = $user;

            // Get avaliable notifier modules for User
            $user_notifiers = UserNotifier::getAllowedNotifier($user->id, $message_type);
            foreach ($user_notifiers as $notifier) {
                self::sendNotifier($notifier->module, $message_type, $message_data);
            }
        }
    }


    public static function sendToManagersNew(callable $callback, array $message_data)
    {

        // User List to notifier
        $user_managers = User::getUsers(['manager' => 1]);
        foreach ($user_managers as $user) {
            $message_data['user'] = $user;

            [$class, $method] = $callback;

            // Get avaliable notifier modules for User
            $user_notifiers = UserNotifier::getAllowedNotifier($user->id, $method);
            foreach ($user_notifiers as $notifier) {
                self::sendNotifierNew($notifier->module, $callback, $message_data);
            }
        }
    }


    public static function sendNotifierNew(string $module_name, callable $callback, array $message_data)
    {

        [$class, $method] = $callback;

        // Проверить есть ли такой method (message function)
        if (!class_exists($class) || !method_exists($class, $method)) {
            return;
        }

        // Get module settings
        $notifier_settings  = UserNotifier::getNotifierSettings($module_name);
        $message_data       = array_merge((array) $notifier_settings, $message_data);

        // Fetch template
        $message_content = $class::$method($module_name, $message_data);

        // Run
        $Module = "HugaShop\\Modules\\Notifier\\{$module_name}\\{$module_name}";
        return $Module::send($message_content, $message_data);
    }




    /**
     * Get notifier messages
     * @param string $entity
     */
    public static function getNotifierMessages(string $type)
    {
        $messages = self::$message_types[$type] ?? [];

        // Get extra notifier messages from extensions
        $extensions = Helper::getModules(Config::get('extension_dir'));
        foreach ($extensions as $extension) {
            if (!empty($extension->notifier->$type)) {
                foreach ((array) $extension->notifier->$type as $message) {
                    foreach ((array) $message as $message_key => $message_params) {
                        $messages[$message_key] = [
                            'name' => $message_params->name,
                            'class' => $message_params->class
                        ];
                    }
                }
            }
        }

        return $messages;
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
     * Send Comment to Admin
     *
     * @param string $template_path
     * @param array $message_data
     */
    public static function commentToAdmin(string $template_path, array &$message_data)
    {

        if (empty($message_data['comment_id']) || empty($comment = ContentComment::getOne($message_data['comment_id']))) {
            return false;
        }

        if ($comment->type == Product::class) {
            $comment->product = Product::getProduct(intval($comment->entity_id));
        }
        if ($comment->type == ContentPost::class) {
            $comment->post = ContentPost::getOne($comment->entity_id);
        }

        Design::assign('comment', $comment);

        // Image template
        $template = Design::fetch($template_path);
        $message_data['subject'] = Design::getTemplateVars('subject');

        return $template;
    }


    /**
     * Notification adout New Order for Admin
     *
     * @param string $template_path
     * @param array $message_data
     */
    public static function newOrderToAdmin(string $module_name, array &$message_data)
    {
        if (empty($message_data['order_id']) || empty($order = Order::getOrder(intval($message_data['order_id'])))) {
            return false;
        }

        $module_dir       = Config::get('notifier_dir') . "$module_name/";
        $template_path    = $module_dir . "templates/newOrderToAdmin.tpl";

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
        $message_data['subject'] = Design::getTemplateVars('subject');

        // Link in Button
        $message_data['url_text'] = Design::getTemplateVars('url_text');
        $message_data['url'] = Design::getTemplateVars('url');

        return $template;
    }


    /**
     * Notification adout New Order for User
     *
     * @param string $template_path
     * @param array $message_data
     */
    public static function newOrderToUser(string $template_path, array &$message_data)
    {

        if (empty($message_data['order_id']) || empty($order = Order::getOrder(intval($message_data['order_id']))) || empty($order->email)) {
            return false;
        }

        $purchases = OrderPurchase::getPurchases(['order_id' => $order->id], ['image', 'product']);

        // Get Delivery and Payment methods
        $payment_method     = OrderPayment::getOne($order->payment_method_id);
        $delivery_method    = OrderDelivery::getOne($order->delivery_id);


        Design::assign([
            'order' => $order,
            'purchases' => $purchases,
            'payment_method' => $payment_method,
            'delivery_method' => $delivery_method
        ]);

        // Image template
        $template = Design::fetch($template_path);
        $message_data['subject'] = Design::getTemplateVars('subject');

        return $template;
    }


    /**
     * Send code for Passwords Remind
     *
     * @param string $template_path
     * @param array $message_data
     */
    public static function passwordRemindToUser(string $template_path, array &$message_data)
    {

        if (empty($message_data['user_id']) || empty($user = User::getUser($message_data['user_id']))) {
            return false;
        }

        Design::assign([
            'user' => $user,
            'code' => $message_data['code']
        ]);

        // Image template
        $template = Design::fetch($template_path);
        $message_data['subject'] = Design::getTemplateVars('subject');

        Design::clearAssign('user');
        Design::clearAssign('code');

        return $template;
    }


    /**
     * Send Delivey Track Number to User
     *
     * @param string $template_path
     * @param array $message_data
     */
    public static function deliveryTrackNumberToUser(string $template_path, array &$message_data)
    {

        if (empty($message_data['order_id']) || empty($order = Order::getOrder(intval($message_data['order_id'])))) {
            return false;
        }

        $message_data['order'] = $order;
        Design::assign('order', $order);

        // Image template
        $template = Design::fetch($template_path);

        return $template;
    }


    /**
     * Send Payment Details to User
     *
     * @param string $template_path
     * @param array $message_data
     */
    public static function paymentDetailsToUser(string $template_path, array &$message_data)
    {

        if (empty($message_data['order_id']) || empty($order = Order::getOrder(intval($message_data['order_id'])))) {
            return false;
        }

        $message_data['order'] = $order;

        // Выбираем указаный способ оплаты
        $payment_method     = OrderPayment::getOne($order->payment_method_id);
        $payment_currency   = FinanceCurrency::getCurrency(intval($payment_method->currency_id));
        $payment_settings   = $payment_method->settings;

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
