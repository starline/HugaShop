<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 3.1
 *
 */

namespace HugaShop\Extensions\TestScript\Controller;

use OpenAI;
use stdClass;
use HugaShop\Models\Image;
use HugaShop\Models\Settings;
use HugaShop\Services\Config;
use HugaShop\Services\Design;
use HugaShop\Services\Helper;
use HugaShop\Models\User\User;
use HugaShop\Services\Request;
use HugaShop\Models\Order\Order;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Address;
use HugaShop\Models\Product\Product;
use HugaShop\Models\Cart\CartPurchase;
use HugaShop\Models\User\UserNotifier;
use Symfony\Component\Process\Process;
use App\Controller\BaseAdminController;
use Symfony\Component\Mailer\Transport;
use HugaShop\Models\Content\ContentPost;
use HugaShop\Models\Order\OrderPurchase;
use HugaShop\Extensions\BaseExtensionTrait;
use HugaShop\Models\Content\ContentComment;
use HugaShop\Models\Product\ProductRelated;
use HugaShop\Models\Product\ProductCategory;
use HugaShop\Models\Warehouse\WarehouseMove;
use Symfony\Component\Filesystem\Filesystem;
use HugaShop\Models\Warehouse\WarehousePlace;
use Illuminate\Database\Capsule\Manager as DB;
use Symfony\Component\Routing\Attribute\Route;
use HugaShop\Models\Warehouse\WarehouseProduct;
use Symfony\Component\Console\Input\ArrayInput;
use HugaShop\Models\Warehouse\WarehousePurchase;
use Symfony\Component\Mailer\Transport\Transports;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Console\Output\BufferedOutput;
use HugaShop\Extensions\TestScript\Services\Composer;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use HugaShop\Extensions\TestScript\Services\SystemCheck;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

final class TestScriptController extends BaseAdminController
{

    use BaseExtensionTrait;

    /**
     * Список странниц
     */
    #[Route('/TestScript', name: 'ExtTestScript', priority: 20)]
    public function test()
    {

        $result = ['First row'];

        // Обработка действий
        if (Request::checkCSRF()) {

            switch (Request::post('action')) {

                case 'script': { # Выполнить скрипт


                        // Symfony Command 
                        if (0) {

                            $application = new Application($this->container->get('kernel'));
                            $application->setAutoExit(false);

                            $input = new ArrayInput([
                                'command' => 'asset-map:compile',
                                //'command' => 'cache:clear',
                                // (optional) define the value of command arguments
                                //'fooArgument' => 'barValue',
                                // (optional) pass options to the command
                                //'--bar' => 'fooValue',
                                // (optional) pass options without value
                                //'--help' => true,
                            ]);

                            // You can use NullOutput() if you don't need the output
                            $output = new BufferedOutput();
                            $application->run($input, $output);

                            // return the output, don't use if you used NullOutput()
                            $result[] = $output->fetch();
                        }


                        // Session
                        if (0) {
                            Request::setSession('test', [2 => 'два']);
                            $result[] = Request::getSession('test')[2];
                        }


                        // Mailer
                        if (0) {

                            // Works well
                            $result[] = UserNotifier::sendNotifierToManager('newOrderToAdmin', [
                                'order_id' => 5172
                            ]);

                            if (0) {
                                $transport = Transport::fromDsn('sendmail://default');
                                //$transport = Transport::fromDsn('native://default');
                                //$transport = Transport::fromDsn('smtp://localhost');

                                //$mailer = new Mailer($transport);
                                $mailer = new Transports([$transport]);

                                $email = (new Email())
                                    ->from(new Address('notify@grizlicnc.com.ua', 'Grizlicnc.com.ua'))
                                    ->to('aa.guzhva@gmail.com')
                                    ->subject('Script Time for Symfony Mailer!')
                                    ->text('Sending emails is fun again!')
                                    ->html('<p>See Twig integration for better HTML integration!</p>');

                                //$result[] = $mailer;

                                try {
                                    $send = $mailer->send($email);
                                    //dump($send->getDebug());
                                } catch (TransportExceptionInterface $e) {
                                    // some error prevented the email sending; display an
                                    // error message or try to resend the message
                                    $result[] = $e;
                                }
                            }
                        }


                        // Cookies
                        if (0) {
                            $result[] = Request::getCookie('_fbp', false);
                            $result[] = \Symfony\Component\HttpFoundation\Request::createFromGlobals()->cookies->get('_fbp');
                        }


                        // Time zone
                        if (0) {

                            //$result[] = UserCoupon::countCoupons(['valid' => 1]);

                            // date_default_timezone_set('UTC');
                            $result[] = 'UTC Time: ' . date("Y-m-d H:i:s") . '. Timezone: ' .  date_default_timezone_get();

                            $from_date = '2020-12-10';
                            $result[] = $date = new \DateTime($from_date . ' ' . Settings::getParam('timezone'));
                            //$date->setTimeZone(new \DateTimeZone('UTC'));

                            //$result[] = $date = new \DateTime($from_date);
                            //$timezone = new \DateTimeZone(Settings::getParam('timezone));
                            //$date->setTimeZone($timezone);

                            $result[] = 'Server Time: ' . $date->format('Y-m-d H:i:s') . '. Timezone: ' .  Settings::getParam('timezone');
                        }


                        // Make token for all users
                        if (0) {
                            $users = User::getUsers();
                            foreach ($users as $user) {
                                $token = Helper::makeToken($user->id);
                                User::updateUser($user->id, ['token' => $token]);
                            }

                            $result[] = 'Set token';
                        }


                        // OpenAI
                        if (0) {
                            $key = Config::get('openai')->key;
                            //$client = OpenAI::client(Settings::getParam('openai.key'));

                            $client = OpenAI::client($key);

                            $result = $client->chat()->create([
                                'model' => 'gpt-4o',
                                'messages' => [
                                    ['role' => 'user', 'content' => 'напиши мне описание для для товара в интернет магазин на тему "Шпиндель ZY 1.5кВт 220В цанга ER16 водяное охлаждение (4 подшипника)"'],
                                ],
                            ]);

                            $result[] = $result->choices[0]->message->content;;
                        }


                        // Telegram BOT Send message
                        if (0) {
                            $result[] = $_SERVER['SERVER_NAME'];
                            $result[] = UserNotifier::sendNotifierToManager('newOrderToAdmin', [
                                'order_id' => 5172
                            ]);
                        }


                        // Выводим ошибки
                        if (0) {
                            $result = trigger_error('my error', E_USER_WARNING);
                            function myErrorHandler($errno, $errstr, $errfile, $errline)
                            {
                                print "[$errno] $errstr";
                            }

                            // set to the user defined error handler
                            set_error_handler("myErrorHandler");
                        }


                        // Работаем с Object
                        if (0) {

                            // PHP_EOL - перевод строки
                            $obj = new \stdClass();
                            if (empty($obj)) { # Result: Not empty
                                $result[] = 'Object empty';
                            } else {
                                $result[] = $obj;
                            }

                            $message_params = array();
                            $message_params['var1'] = 'var1 - old value';
                            $message_params['var1'] = isset($message_params['not exist']) ? $message_params['not exist'] : 'var1 - new value';
                            $result[] = $message_params['var1']; # var1 - new value

                            //$message_params['var2'] = 'var2 - old value';
                            $message_params['var2'] = null;
                            $message_params['var2'] = $message_params['var2'] ?: 'var2 - new value'; # Change if empty|false|[]|0|null - should be isset
                            $result[] = $message_params['var2'];

                            //$message_params['var3'] = 'var3 - old value';
                            //$message_params['var3'] = 0;
                            $message_params['var3'] = $message_params['var3'] ?? 'var3 - new value'; # Change if null|NO isset
                            $result[] = $message_params['var3'];

                            $settings = null;
                            if (isset($settings) and is_null($settings)) {
                                $result[] = 'get NULL';
                            }

                            $test_arr = ['var1' => 'value1', 'var2', 'var3', 'var4' => 'value4', 'var5'];
                            $result[] = $test_arr;
                            foreach ($test_arr as $key => $val) {
                                $result[] = $key . ' ' . $val;
                            }
                        }


                        // Threw the function
                        if (0) {

                            $arr = ['var1' => 'old value', '$var2' => 2];
                            $arr_copy = $arr;
                            function bridge1(&$params)
                            {
                                $params['var1'] = 'new value';
                            }
                            bridge1($arr);


                            $arr2 = ['var1' => 'old value', '$var2' => 2];
                            function bridge2(array $params)
                            {
                                $params['var1'] = 'new value';
                            }
                            bridge2($arr2);


                            $obj = new \stdClass();
                            $obj->var1 = 'old value';
                            function bridge3($params)
                            {
                                $params->var1 = 'new value';
                            }
                            bridge3($obj);


                            function checkFunction(?int $param = null)
                            {
                                return 'check param: ' . $param;
                            }
                            $result[] = checkFunction();


                            $result[] = $arr['var1'];       # Linking var threw the function with '&'
                            $result[] = $arr_copy['var1'];  # Array doesn't linking with =
                            $result[] = $arr2['var1'];      # NO linking Array threw the function
                            $result[] = $obj->var1;         # Linking Object threw the function
                        }


                        /**
                         * Переход на версию V2
                         * 1. Исправляем типы комментарий 
                         * 2. делаем дубликат таблицы s_product_variant в s_product_variant_temp
                         * 3. Переносим варианты в таблицу product
                         * 
                         * Удалить cart_purchase.variant_id
                         * Удалить order_purchase.variant_id
                         * Удалить wh_move_purchase.variant_id
                         * 
                         */
                        if (1) {

                            // 1. Исправляем типы комментарий
                            if (0) {
                                ContentComment::chunk(100, function ($comments) { #  обрабатывает по 100 записей за раз, чтобы не съесть всю память
                                    foreach ($comments as $comment) {
                                        if (!empty($comment->type)) {
                                            if ($comment->type == 'blog') {
                                                $comment->entity_type = ContentPost::class;
                                            }
                                            if ($comment->type == 'product') {
                                                $comment->entity_type = Product::class;
                                            }
                                            $comment->save();
                                        }
                                    }
                                });

                                $result[] = 'done';
                            }

                            // 2. Перенести варианты в товары
                            // 3. Создать колонку variant_id в таблице product
                            if (0) {

                                //$products = Product::query()->orderByDesc('position')->limit(3)->get();

                                Product::chunk(100, function ($products) { #  обрабатывает по 100 записей за раз, чтобы не съесть всю память
                                    foreach ($products as $product) {
                                        $variants = DB::table('s_product_variant_temp')
                                            ->where('product_id', $product->id)
                                            ->get();

                                        if ($variants->isEmpty()) {
                                            continue;
                                        }

                                        // Если только один вариант
                                        // Переносим цены и наличие в таблицу product
                                        $variant = $variants->first();

                                        $product_upd = [];
                                        $product_upd['sku'] =           $variant->sku;
                                        $product_upd['price'] =         $variant->price;
                                        $product_upd['cost_price'] =    $variant->cost_price;
                                        $product_upd['old_price'] =     $variant->old_price;
                                        $product_upd['stock'] =         $variant->stock;
                                        $product_upd['weight'] =        $variant->weight;
                                        $product_upd['awaiting_date'] = $variant->awaiting_date;
                                        $product_upd['awaiting'] =      $variant->awaiting;
                                        $product_upd['custom'] =        $variant->custom;
                                        $product_upd['variant_id'] =    $variant->id;
                                        $product_upd['variant_name'] =  $variant->name;

                                        Product::where('id', $product->id)->update($product_upd);

                                        // Если есть еще варианты
                                        $variants = $variants->slice(1)->values(); // Остальные варианты (без первого)

                                        foreach ($variants as $variant) {

                                            // Дублируем товар
                                            $new_product_id = Product::duplicateProduct($product->id);

                                            // Обновляем новый товар
                                            $product_new = new stdClass();
                                            $product_new->created =       $variant->date;
                                            $product_new->variant_id =    $variant->id;
                                            $product_new->sku =           $variant->sku;
                                            $product_new->price =         $variant->price;
                                            $product_new->cost_price =    $variant->cost_price;
                                            $product_new->old_price =     $variant->old_price;
                                            $product_new->stock =         $variant->stock;
                                            $product_new->weight =        $variant->weight;
                                            $product_new->awaiting_date = $variant->awaiting_date;
                                            $product_new->awaiting =      $variant->awaiting;
                                            $product_new->custom =        $variant->custom;
                                            $product_new->variant_name =  $variant->name;

                                            Product::updateOne($new_product_id, $product_new);

                                            $result[] = $product_new;
                                        }
                                    }
                                });
                            }

                            // 3. Заменняем product_id на новые по variant_id d WarehousePurchase
                            // 4. Удаляем variant_id в WarehousePurchase
                            if (0) {
                                $w_purchases = WarehousePurchase::query()->get();
                                foreach ($w_purchases as $w_purchase) {

                                    $product = Product::where('variant_id', $w_purchase->variant_id)->first();

                                    if ($product and $w_purchase->product_id != $product->id) {
                                        $result[] = $w_purchase->product_id . ' -> ' . $product->id;
                                        $w_purchase->product_id = $product->id;
                                        $w_purchase->save();
                                    }
                                }
                            }


                            // 5. Заменняем product_id на новые по variant_id d OrderPurchase
                            // 4. Удаляем variant_id в WarehousePurchase
                            if (0) {
                                $purchases = OrderPurchase::query()->get();
                                foreach ($purchases as $purchase) {

                                    $product = Product::where('variant_id', $purchase->variant_id)->first();

                                    if ($product and $purchase->product_id != $product->id) {
                                        $result[] = $purchase->product_id . ' -> ' . $product->id;
                                        $purchase->product_id = $product->id;
                                        $purchase->save();
                                    }
                                }
                            }

                            // Тоже самое для Cart
                            if (0) {
                                $purchases = CartPurchase::query()->get();
                                foreach ($purchases as $purchase) {

                                    $product = Product::where('variant_id', $purchase->variant_id)->first();

                                    if ($product and $purchase->product_id != $product->id) {
                                        $result[] = $purchase->product_id . ' -> ' . $product->id;
                                        $purchase->product_id = $product->id;
                                        $purchase->save();
                                    }
                                }
                            }


                            // В ProductRelated удалить $table 
                            // В таблице переименовать product_product_relates на product_related

                            // Перенос всех товаров в первый склад по списку
                            if (0) {
                                $place = WarehousePlace::getList(order: 'position')->first();
                                if ($place) {
                                    Product::chunk(100, function ($products) use ($place) {
                                        foreach ($products as $product) {
                                            WarehouseProduct::createOne([
                                                'product_id' => $product->id,
                                                'place_id'   => $place->id,
                                                'cost_price' => $product->cost_price ?? 0,
                                                'amount'     => $product->stock ?? 0,
                                            ]);
                                        }
                                    });

                                    $result[] = 'All products moved to warehouse #' . $place->id;
                                } else {
                                    $result[] = 'No warehouse places found';
                                }
                            }


                            // Установим для всех поставкок где не указан склад, первый склад
                            if (0) {
                                $place = WarehousePlace::getList(order: 'position')->first();
                                if ($place) {
                                    WarehouseMove::query()
                                        ->whereNull('place_id')
                                        ->chunk(50, function ($moves) use ($place) {
                                            foreach ($moves as $move) {
                                                $move->place_id = $place->id;
                                                $move->save();
                                            }
                                        });

                                    $result[] = 'All undefined movementt to warehouse #' . $place->id;
                                } else {
                                    $result[] = 'No warehouse places found';
                                }
                            }


                            // переносим изображения
                            if (0) {
                                Image::query()
                                    ->where('entity_name', 'product_content')
                                    ->chunk(100, function ($images) {
                                        foreach ($images as $image) {
                                            $image->entity_name = 'product';
                                            $image->visible = 0;
                                            $image->save();
                                        }
                                    });

                                $result[] = 'Images changed to type product';
                            }

                            if (0) {
                                Image::query()
                                    ->where('entity_name', 'category_content')
                                    ->chunk(100, function ($images) {
                                        foreach ($images as $image) {
                                            $image->entity_name = 'category';
                                            $image->visible = 0;
                                            $image->save();
                                        }
                                    });

                                $result[] = 'Images changed to type category';
                            }


                            // Сортировка изображений
                            if (0) {
                                Product::chunk(100, function ($products) {
                                    foreach ($products as $product) {
                                        $images = Image::query()
                                            ->where('entity_name', 'product')
                                            ->where('entity_id', $product->id)
                                            ->orderBy('position')
                                            ->get();

                                        if ($images->isNotEmpty()) {
                                            $position = 0;
                                            foreach ($images->where('visible', 1) as $image) {
                                                $image->position = ++$position;
                                                $image->save();
                                            }
                                            foreach ($images->where('visible', 0) as $image) {
                                                $image->position = ++$position;
                                                $image->save();
                                            }
                                        }
                                    }
                                });

                                $result[] = 'Images sorted for all products';
                            }


                            $result[] = 'done';
                        }

                        break;
                    }


                    // Подбираем сопутсвующие товары
                case 'related_products': {

                        // Выбираем все товары, активные
                        $products = Product::getProducts(array("visible" => true));
                        //$products = Product::getProducts(array("visible" => true, "id" => 434));

                        foreach ($products as $product) {

                            // Выбираем все текущие связанные товары
                            //$cur_rel_products = ProductRelated::getRelatedProducts($product->id);
                            $cur_rel_products = [];

                            // Выбираем Все товары в выполненных заказах
                            $purchases_ids = [];
                            $orders_done = Order::getOrders(['product_id' => $product->id]);
                            if (!empty($orders_done)) {
                                $purchases = OrderPurchase::getPurchases(['order_id' => array_keys($orders_done)]);
                                foreach ($purchases as $pur) {
                                    $purchases_ids[] = $pur->product_id;
                                }
                            }

                            // Соединяем выбранные товары. убираем дубликаты
                            $rel_products_ids = array_unique(array_merge(array_keys($cur_rel_products), $purchases_ids));

                            // Выбираем все товары в категории
                            // Если товаров меньше чем в настройках, выбираем все товары родительской категории
                            $category_products = [];
                            $parent_category_products = [];
                            if (count($rel_products_ids) < Settings::getParam('rel_products_num')) {
                                $category_products = Product::getProducts(array("visible" => true, "in_stock" => true, "category_id" => $product->category_id));
                                $rel_products_ids = array_unique(array_merge($rel_products_ids, array_keys($category_products)));

                                if (count($rel_products_ids) < Settings::getParam('rel_products_num') and !empty($product->category_id)) {
                                    $category = ProductCategory::getCategoryById($product->category_id);
                                    $parent_category = ProductCategory::getCategoryById($category->parent_id);
                                    if (!empty($parent_category->children)) {
                                        $parent_category_products = Product::getProducts(array("visible" => true, "in_stock" => true, "category_id" => $parent_category->children));
                                        $rel_products_ids = array_unique(array_merge($rel_products_ids, array_keys($parent_category_products)));
                                    }
                                }
                            }

                            // Проверяем что товары в наличии, активны, продажи. Сортировка по рентабельности
                            $active_products = Product::getProducts(array("id" => $rel_products_ids, "visible" => true, "in_stock" => true, "top" => true, "date_from" => date('Y-m-d', strtotime('-180 days'))));
                            $rel_products_ids = array_keys($active_products);

                            // Если товаров мало, добавляем
                            if (count($rel_products_ids) < Settings::getParam('rel_products_num')) {
                                $rel_products_ids = array_unique(array_merge($rel_products_ids, array_keys($cur_rel_products)));
                                if (count($rel_products_ids) < Settings::getParam('rel_products_num')) {
                                    $rel_products_ids = array_unique(array_merge($rel_products_ids, $purchases_ids));
                                    if (count($rel_products_ids) < Settings::getParam('rel_products_num')) {
                                        $rel_products_ids = array_unique(array_merge($rel_products_ids, array_keys($category_products)));
                                        if (count($rel_products_ids) < Settings::getParam('rel_products_num')) {
                                            $rel_products_ids = array_unique(array_merge($rel_products_ids, array_keys($parent_category_products)));
                                        }
                                    }
                                }
                            }

                            // Проверяем что товары в наличии, активны
                            $new_active_products = Product::getProducts(array("id" => $rel_products_ids, "visible" => true, "in_stock" => true));
                            $rel_products_ids = array_unique(array_merge(array_keys($active_products), array_keys($new_active_products)));

                            // Удаляем текущий товар из выбрки
                            foreach ($rel_products_ids as $index => $value) {
                                if ($value == $product->id) {
                                    unset($rel_products_ids[$index]);
                                }
                            }

                            // Обрезаем выборку до максимального кол-ва для показа
                            $rel_products_ids = array_slice($rel_products_ids, 0, Settings::getParam('rel_products_num') + 2);
                            //print_r($rel_products_ids);

                            // Удаляем все связанные товары
                            ProductRelated::deleteAllRelatedProducts($product->id);

                            // Записываем новые связаные товары
                            $pos = 0;
                            foreach ($rel_products_ids as $rel_id) {
                                Product::addRelatedProduct($product->id, $rel_id, $pos++);
                            }
                        }
                        break;
                    }


                    // Исправляем базу заказов
                case 'restore_orders': {
                        $orders = Order::getList();
                        foreach ($orders as $order) {

                            // Просчитываем payment_price
                            if (0) {
                                Order::updateTotalPrice($order->id, false);
                            }

                            // Переносим поле sms_payment_info в settings
                            if (0 and isset($order->sms_payment_info) and $order->sms_payment_info == 1) {
                                $order->settings->payment_sms = $order->sms_payment_info;
                                Order::updateOrder($order->id, ['settings' => $order->settings], false);
                            }

                            // Переносим поле sms_delivery_note в settings
                            if (0 and isset($order->sms_delivery_note) and $order->sms_delivery_note == 1) {
                                $order->settings->delivery_sms = $order->sms_delivery_note;
                                Order::updateOrder($order->id, array('settings' => $order->settings), false);
                            }

                            // Переносим поле delivery_info в settings
                            if (0 and !empty($order->delivery_info)) {
                                $order->settings->delivery_info = $order->delivery_info;
                                Order::updateOrder($order->id, array('settings' => $order->settings), false);
                            }
                        }
                        break;
                    }


                    // Check php settings
                case 'php_check': {
                        Design::assign('php_check', SystemCheck::checkPhp());
                        break;
                    }


                    // Composer update and Очистить КЕШ
                case 'cache_clear': {
                        $result = array_merge($result, Composer::allUpdate());
                        break;
                    }


                    // Assets Clear
                case 'assets_clear': {
                        $filesystem = new Filesystem();
                        $filesystem->remove([Config::get('root_dir') . 'public/assets/']);

                        $result[] = 'Clear /public/assets';
                    }
            }
        }

        $result = print_r($result, true);
        Design::assign('result', $result);

        return $this->fetchExtResponse('index.tpl');
    }
}
