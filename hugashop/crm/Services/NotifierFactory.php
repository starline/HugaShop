<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 3.0
 *
 */

namespace HugaShop\Services;

use HugaShop\Models\User\User;
use HugaShop\Models\Order\Order;
use HugaShop\Models\Product\Product;
use HugaShop\Models\User\UserNotifier;
use Symfony\Component\Process\Process;
use HugaShop\Models\Order\OrderPayment;
use HugaShop\Models\Content\ContentPost;
use HugaShop\Models\Order\OrderDelivery;
use HugaShop\Models\Order\OrderPurchase;
use HugaShop\Models\Content\ContentComment;
use HugaShop\Models\Finance\FinanceCurrency;
use Symfony\Component\Process\PhpExecutableFinder;

class NotifierFactory
{

    public static $message_types = [
        'user' => [
            'newOrderToUser'              => 'О новом заказе',
            'deliveryTrackNumberToUser'   => 'Трэк номер доставка',
            'paymentDetailsToUser'        => 'Реквизиты об оплате'
        ],
        'admin' => [
            'commentToAdmin'              => 'Новый Комментарий',
            'newOrderToAdmin'             => 'Новый Заказ',
        ],
        'system' => [
            'passwordRemindToUser'        => 'Восстановление пароля'
        ]
    ];


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

        $message_data   = array_merge((array) $notifier_settings, $message_data);
        $Module         = "HugaShop\\Modules\\Notifier\\{$module_name}\\{$module_name}";

        if (empty($module_name) || !class_exists($Module)) {
            return false;
        }

        // Run
        return $Module::send($message_content, $message_data);
    }


    /**
     * Send notification template via Module
     */
    public static function sendNotifier(string $module_name, callable $callback, array $message_data)
    {

        [$class, $method] = $callback;

        // Проверить есть ли такой method (message function)
        if (!class_exists($class) || !method_exists($class, $method)) {
            return false;
        }

        // Get module settings
        $notifier_settings  = UserNotifier::getNotifierSettings($module_name);
        if (empty($notifier_settings)) {
            return false;
        }

        $message_data       = array_merge((array) $notifier_settings, $message_data);
        $Module             = "HugaShop\\Modules\\Notifier\\{$module_name}\\{$module_name}";

        // Fetch template
        $message_content    = $class::$method($module_name, $message_data);

        if (empty($message_content) || !class_exists($Module)) {
            return false;
        }

        // Run in background process
        $root_dir       = Config::get('root_dir');
        $console_path   = $root_dir ? $root_dir . 'bin/console' : null;
        $php_finder     = new PhpExecutableFinder();
        $php_binary     = $php_finder->find(false) ?: PHP_BINARY;

        if (empty($console_path) || !is_file($console_path) || empty($php_binary)) {
            return false;
        }

        // Prepare command
        $command = [
            $php_binary,
            $console_path,
            'notifier:send',
            $module_name,
            base64_encode($message_content),
            base64_encode(serialize($message_data)),
        ];

        try {
            $process = new Process($command, $root_dir);
            $process->setTimeout(null);
            $process->disableOutput();
            $process->start();
        } catch (\Throwable) {
            return false;
        }

        return true;
    }


    /**
     * Send Notifier manager. Select avaliable notifier Method
     * Send notification only to Managers
     */
    public static function sendToManagers(callable $callback, array $message_data)
    {

        // User List to notifier
        $user_managers = User::getUsers(['manager' => 1]);
        foreach ($user_managers as $user) {
            $message_data['user'] = $user;

            [$class, $method] = $callback;

            // Get avaliable notifier modules for User
            $user_notifiers = UserNotifier::getAllowedNotifier($user->id, $method);
            foreach ($user_notifiers as $notifier) {
                self::sendNotifier($notifier->module, $callback, $message_data);
            }
        }
    }


    /**
     * Get notifier messages
     * @param string $entity
     */
    public static function getNotifierMessages(string $type)
    {
        $messages = self::$message_types[$type] ?? [];

        // Get extra notifier messages from addons
        $addons = Helper::getModules(Config::get('addon_dir'));
        foreach ($addons as $addon) {
            if (!empty($addon->notifier->$type)) {
                foreach ((array) $addon->notifier->$type as $message) {
                    foreach ((array) $message as $message_key => $message_name) {
                        $messages[$message_key] = $message_name;
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
     * @param string $module_name
     * @param array $message_data
     */
    public static function commentToAdmin(string $module_name, array &$message_data)
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

        return self::fetchTemplate($module_name, __FUNCTION__);
    }


    /**
     * Notification adout New Order for Admin
     *
     * @param string $module_name
     * @param array $message_data
     */
    public static function newOrderToAdmin(string $module_name, array &$message_data)
    {
        if (empty($message_data['order_id']) || empty($order = Order::getOrder(intval($message_data['order_id'])))) {
            return false;
        }

        Design::assign([
            'order'             => $order,
            'purchases'         => OrderPurchase::getPurchases(['order_id' => $order->id], ['image', 'product']),
            'payment_method'    => OrderPayment::getOne($order->payment_method_id),
            'delivery_method'   => OrderDelivery::getOne($order->delivery_id)
        ]);

        // Image template
        $template = self::fetchTemplate($module_name, __FUNCTION__);

        // Link in Button
        $message_data['subject']    = Design::getTemplateVars('subject');
        $message_data['url_text']   = Design::getTemplateVars('url_text');
        $message_data['url']        = Design::getTemplateVars('url');

        return $template;
    }


    /**
     * Notification adout New Order for User
     *
     * @param string $module_name
     * @param array $message_data
     */
    public static function newOrderToUser(string $module_name, array &$message_data)
    {

        if (empty($message_data['order_id']) || empty($order = Order::getOrder(intval($message_data['order_id']))) || empty($order->email)) {
            return false;
        }

        Design::assign([
            'order'             => $order,
            'purchases'         => OrderPurchase::getPurchases(['order_id' => $order->id], ['image', 'product']),
            'payment_method'    => OrderPayment::getOne($order->payment_method_id),
            'delivery_method'   => OrderDelivery::getOne($order->delivery_id)
        ]);

        return self::fetchTemplate($module_name, __FUNCTION__);
    }


    /**
     * Send code for Passwords Remind
     *
     * @param string $module_name
     * @param array $message_data
     */
    public static function passwordRemindToUser(string $module_name, array &$message_data)
    {

        if (empty($message_data['user_id']) || empty($user = User::getUser($message_data['user_id']))) {
            return false;
        }

        Design::assign([
            'user' => $user,
            'code' => $message_data['code']
        ]);

        // Image template
        $template = self::fetchTemplate($module_name, __FUNCTION__);

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
    public static function deliveryTrackNumberToUser(string $module_name, array &$message_data)
    {

        if (empty($message_data['order_id']) || empty($order = Order::getOrder(intval($message_data['order_id'])))) {
            return false;
        }

        $message_data['order'] = $order;
        Design::assign('order', $order);

        return self::fetchTemplate($module_name, __FUNCTION__);
    }


    /**
     * Send Payment Details to User
     *
     * @param string $module_name
     * @param array $message_data
     */
    public static function paymentDetailsToUser(string $module_name, array &$message_data)
    {

        if (empty($message_data['order_id']) || empty($order = Order::getOrder(intval($message_data['order_id'])))) {
            return false;
        }

        $message_data['order'] = $order;

        // Выбираем указаный способ оплаты
        $payment_method     = OrderPayment::getOne($order->payment_method_id);

        Design::assign([
            'order'             => $order,
            'payment_method'    => $payment_method,
            'payment_currency'  => FinanceCurrency::getCurrency(intval($payment_method->currency_id)),
            'payment_settings'  => $payment_method->settings
        ]);

        return self::fetchTemplate($module_name, __FUNCTION__);
    }


    /**
     * Make template path
     */
    public static function getTemplatePath(string $module_name, string $template_name)
    {
        $path = Config::get('notifier_dir') . "$module_name/templates/$template_name.tpl";
        return file_exists($path) ? $path : null;
    }


    /**
     * Image template
     */
    private static function fetchTemplate(string $module_name, string $template_name)
    {
        $template_path = self::getTemplatePath($module_name, $template_name);
        return $template_path ? Design::fetch($template_path) : null;
    }
}
