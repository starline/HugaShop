<?php

/**
 *
 * @author Andi Huga
 * @version 1.2
 *
 */

namespace App\Controller\Front\Exchange;

use HugaShop\Api\Image;
use HugaShop\Api\Config;
use HugaShop\Api\Helper;
use HugaShop\Api\Request;
use HugaShop\Api\Settings;
use Illuminate\Support\Str;
use HugaShop\Api\Order\Order;
use HugaShop\Api\DatabaseQuery;
use HugaShop\Api\Product\Product;
use HugaShop\Api\Order\OrderPurchase;
use HugaShop\Api\Product\ProductBrand;
use App\Controller\BaseAdminController;
use HugaShop\Api\Product\ProductOption;
use HugaShop\Api\Product\ProductFeature;
use HugaShop\Api\Product\ProductVariant;
use HugaShop\Api\Product\ProductCategory;
use Symfony\Component\Routing\Attribute\Route;
use HugaShop\Api\Product\ProductCategoryFeature;

class CmlExchangeController extends BaseAdminController
{
    #[Route('/exchange/cml', name: 'CmlExchange')]
    public function index()
    {
        // Обновлять все данные при каждой синхронизации
        $full_update = true;

        // Название параметра товара, используемого как бренд
        $brand_option_name = 'Производитель';

        $start_time = microtime(true);
        $max_exec_time = min(30, @ini_get("max_execution_time"));
        if (empty($max_exec_time)) {
            $max_exec_time = 30;
        }

        // Папка для хранения временных файлов синхронизации
        $dir = Config::get('root_dir') . '/var/temp/cml/';

        $GLOBALS['dir'] = $dir;
        $GLOBALS['brand_option_name'] = $brand_option_name;
        $GLOBALS['full_update'] = $full_update;

        if (Request::get('type') == 'sale' && Request::get('mode') == 'checkauth') {
            print "success\n";
            print session_name() . "\n";
            print session_id();
        }

        if (Request::get('type') == 'sale' && Request::get('mode') == 'init') {
            $tmp_files = glob($dir . '*.*');
            if (is_array($tmp_files)) {
                foreach ($tmp_files as $v) {
                    //unlink($v);
                }
            }
            print "zip=no\n";
            print "file_limit=1000000\n";
        }

        if (Request::get('type') == 'sale' && Request::get('mode') == 'file') {
            $filename = Request::get('filename');


            $f = fopen($dir . $filename, 'ab');
            fwrite($f, file_get_contents('php://input'));
            fclose($f);

            $xml = simplexml_load_file($dir . $filename);

            foreach ($xml->Документ as $xml_order) {
                $order = new \stdClass();

                $order->id = $xml_order->Номер;
                $existed_order = Order::getOrder(intval($order->id));

                $order->date = $xml_order->Дата . ' ' . $xml_order->Время;
                $order->name = $xml_order->Контрагенты->Контрагент->Наименование;

                if (isset($xml_order->ЗначенияРеквизитов->ЗначениеРеквизита)) {
                    foreach ($xml_order->ЗначенияРеквизитов->ЗначениеРеквизита as $r) {
                        switch ($r->Наименование) {
                            case 'Проведен':
                                $proveden = ($r->Значение == 'true');
                                break;
                            case 'ПометкаУдаления':
                                $udalen = ($r->Значение == 'true');
                                break;
                        }
                    }
                }

                if ($udalen) {
                    $order->status = 3;
                } elseif ($proveden) {
                    $order->status = 1;
                } elseif (!$proveden) {
                    $order->status = 0;
                }

                if ($existed_order) {
                    Order::updateOrder($order->id, $order);
                } else {
                    $order->id = Order::addOrder($order);
                }

                $purchases_ids = array();

                // Товары
                foreach ($xml_order->Товары->Товар as $xml_product) {
                    $purchase = null;

                    //  Id товара и варианта (если есть) по 1С
                    $product_1c_id = $variant_1c_id = '';
                    @list($product_1c_id, $variant_1c_id) = explode('#', $xml_product->Ид);
                    if (empty($product_1c_id)) {
                        $product_1c_id = '';
                    }
                    if (empty($variant_1c_id)) {
                        $variant_1c_id = '';
                    }

                    // Ищем товар
                    $product_id = DatabaseQuery::query('SELECT id FROM __product WHERE external_id=?', $product_1c_id)->result('id');
                    $variant_id = DatabaseQuery::query('SELECT id FROM __product_variant WHERE external_id=? AND product_id=?', $variant_1c_id, $product_id)->result('id');

                    $purchase = new \stdClass();
                    $purchase->order_id = $order->id;
                    $purchase->product_id = $product_id;
                    $purchase->variant_id = $variant_id;

                    $purchase->sku = $xml_product->Артикул;
                    $purchase->product_name = $xml_product->Наименование;
                    $purchase->amount = $xml_product->Количество;
                    $purchase->price = floatval($xml_product->ЦенаЗаЕдиницу);

                    if (isset($xml_product->Скидки->Скидка)) {
                        $discount = $xml_product->Скидки->Скидка->Процент;
                        $purchase->price = $purchase->price * (100 - $discount) / 100;
                    }

                    $purchase_id = DatabaseQuery::query('SELECT id FROM __order_purchase WHERE order_id=? AND product_id=? AND variant_id=?', $order->id, $product_id, $variant_id)->result('id');
                    if (!empty($purchase_id)) {
                        $purchase_id = OrderPurchase::updatePurchase($purchase_id, $purchase);
                    } else {
                        $purchase_id = OrderPurchase::addPurchase($purchase);
                    }
                    $purchases_ids[] = $purchase_id;
                }

                // Удалим покупки, которых нет в файле
                foreach (OrderPurchase::getPurchases(['order_id' => intval($order->id)]) as $purchase) {
                    if (!in_array($purchase->id, $purchases_ids)) {
                        OrderPurchase::deletePurchase($purchase->id);
                    }
                }

                DatabaseQuery::query('UPDATE __order SET discount=0, total_price=? WHERE id=? LIMIT 1', $xml_order->Сумма, $order->id);
            }


            print "success";
            Settings::set('last_1c_orders_export_date', date("Y-m-d H:i:s"));
        }

        if (Request::get('type') == 'sale' && Request::get('mode') == 'query') {
            $no_spaces = '<?xml version="1.0" encoding="utf-8"?>
							<КоммерческаяИнформация ВерсияСхемы="2.04" ДатаФормирования="' . Helper::dateFormat($order->date, 'Y-m-d') . '"></КоммерческаяИнформация>';
            $xml = new \SimpleXMLElement($no_spaces);

            $orders = Order::getOrders(['modified_since' => Settings::getParam('last_1c_orders_export_date')]);
            foreach ($orders as $order) {

                $doc = $xml->addChild("Документ");
                $doc->addChild("Ид", $order->id);
                $doc->addChild("Номер", $order->id);
                $doc->addChild("Дата", Helper::dateFormat($order->date, 'Y-m-d'));
                $doc->addChild("ХозОперация", "Заказ товара");
                $doc->addChild("Роль", "Продавец");
                $doc->addChild("Курс", "1");
                $doc->addChild("Сумма", $order->total_price);
                $doc->addChild("Время", Helper::timeFormat($order->date, 'H:i:s'));
                $doc->addChild("Комментарий", $order->comment);


                // Контрагенты
                $k1 = $doc->addChild('Контрагенты');
                $k1_1 = $k1->addChild('Контрагент');
                $k1_2 = $k1_1->addChild("Ид", $order->name);
                $k1_2 = $k1_1->addChild("Наименование", $order->name);
                $k1_2 = $k1_1->addChild("Роль", "Покупатель");
                $k1_2 = $k1_1->addChild("ПолноеНаименование", $order->name);

                // Доп параметры
                $addr = $k1_1->addChild('АдресРегистрации');
                $addr->addChild('Представление', $order->address);
                $addrField = $addr->addChild('АдресноеПоле');
                $addrField->addChild('Тип', 'Страна');
                $addrField->addChild('Значение', 'RU');
                $addrField = $addr->addChild('АдресноеПоле');
                $addrField->addChild('Тип', 'Регион');
                $addrField->addChild('Значение', $order->address);

                $contacts = $k1_1->addChild('Контакты');
                $cont = $contacts->addChild('Контакт');
                $cont->addChild('Тип', 'Телефон');
                $cont->addChild('Значение', $order->phone);
                $cont = $contacts->addChild('Контакт');
                $cont->addChild('Тип', 'Почта');
                $cont->addChild('Значение', $order->email);

                $purchases = OrderPurchase::getPurchases(['order_id' => intval($order->id)]);

                $t1 = $doc->addChild('Товары');
                foreach ($purchases as $purchase) {
                    if (!empty($purchase->product_id) && !empty($purchase->variant_id)) {
                        $id_p = DatabaseQuery::query('SELECT external_id FROM __product WHERE id=?', $purchase->product_id)->result('external_id');
                        $id_v = DatabaseQuery::query('SELECT external_id FROM __product_variant WHERE id=?', $purchase->variant_id)->result('external_id');

                        // Если нет внешнего ключа товара - указываем наш id
                        if (!empty($id_p)) {
                            $id = $id_p;
                        } else {
                            DatabaseQuery::query('UPDATE __product SET external_id=id WHERE id=?', $purchase->product_id);
                            $id = $purchase->product_id;
                        }

                        // Если нет внешнего ключа варианта - указываем наш id
                        if (!empty($id_v)) {
                            $id = $id . '#' . $id_v;
                        } else {
                            DatabaseQuery::query('UPDATE __product_variant SET external_id=id WHERE id=?', $purchase->variant_id);
                            $id = $id . '#' . $purchase->variant_id;
                        }

                        $t1_1 = $t1->addChild('Товар');

                        if ($id) {
                            $t1_2 = $t1_1->addChild("Ид", $id);
                        }

                        $t1_2 = $t1_1->addChild("Артикул", $purchase->sku);

                        $name = $purchase->product_name;
                        if ($purchase->variant_name) {
                            $name .= " $purchase->variant_name $id";
                        }
                        $t1_2 = $t1_1->addChild("Наименование", $name);
                        $t1_2 = $t1_1->addChild("ЦенаЗаЕдиницу", $purchase->price * (100 - $order->discount) / 100);
                        $t1_2 = $t1_1->addChild("Количество", $purchase->amount);
                        $t1_2 = $t1_1->addChild("Сумма", $purchase->amount * $purchase->price * (100 - $order->discount) / 100);

                        /*
                            $t1_2 = $t1_1->addChild ( "Скидки" );
                            $t1_3 = $t1_2->addChild ( "Скидка" );
                            $t1_4 = $t1_3->addChild ( "Сумма", $purchase->amount*$purchase->price*(100-$order->discount)/100);
                            $t1_4 = $t1_3->addChild ( "УчтеноВСумме", "true" );
                            */

                        $t1_2 = $t1_1->addChild("ЗначенияРеквизитов");
                        $t1_3 = $t1_2->addChild("ЗначениеРеквизита");
                        $t1_4 = $t1_3->addChild("Наименование", "ВидНоменклатуры");
                        $t1_4 = $t1_3->addChild("Значение", "Товар");

                        $t1_2 = $t1_1->addChild("ЗначенияРеквизитов");
                        $t1_3 = $t1_2->addChild("ЗначениеРеквизита");
                        $t1_4 = $t1_3->addChild("Наименование", "ТипНоменклатуры");
                        $t1_4 = $t1_3->addChild("Значение", "Товар");
                    }
                }

                // Доставка
                if ($order->delivery_price > 0 && !$order->separate_delivery) {
                    $t1 = $t1->addChild('Товар');
                    $t1->addChild("Ид", 'ORDER_DELIVERY');
                    $t1->addChild("Наименование", 'Доставка');
                    $t1->addChild("ЦенаЗаЕдиницу", $order->delivery_price);
                    $t1->addChild("Количество", 1);
                    $t1->addChild("Сумма", $order->delivery_price);
                    $t1_2 = $t1->addChild("ЗначенияРеквизитов");
                    $t1_3 = $t1_2->addChild("ЗначениеРеквизита");
                    $t1_4 = $t1_3->addChild("Наименование", "ВидНоменклатуры");
                    $t1_4 = $t1_3->addChild("Значение", "Услуга");

                    $t1_2 = $t1->addChild("ЗначенияРеквизитов");
                    $t1_3 = $t1_2->addChild("ЗначениеРеквизита");
                    $t1_4 = $t1_3->addChild("Наименование", "ТипНоменклатуры");
                    $t1_4 = $t1_3->addChild("Значение", "Услуга");
                }


                // Статус
                if ($order->status == 1) {
                    $s1_2 = $doc->addChild("ЗначенияРеквизитов");
                    $s1_3 = $s1_2->addChild("ЗначениеРеквизита");
                    $s1_3->addChild("Наименование", "Статус заказа");
                    $s1_3->addChild("Значение", "[N] Принят");
                }
                if ($order->status == 2) {
                    $s1_2 = $doc->addChild("ЗначенияРеквизитов");
                    $s1_3 = $s1_2->addChild("ЗначениеРеквизита");
                    $s1_3->addChild("Наименование", "Статус заказа");
                    $s1_3->addChild("Значение", "[F] Доставлен");
                }
                if ($order->status == 3) {
                    $s1_2 = $doc->addChild("ЗначенияРеквизитов");
                    $s1_3 = $s1_2->addChild("ЗначениеРеквизита");
                    $s1_3->addChild("Наименование", "Отменен");
                    $s1_3->addChild("Значение", "true");
                }
            }

            header("Content-type: text/xml; charset=utf-8");
            print "\xEF\xBB\xBF";

            print $xml->asXML();

            Settings::set('last_1c_orders_export_date', date("Y-m-d H:i:s"));
        }

        if (Request::get('type') == 'sale' && Request::get('mode') == 'success') {
            Settings::set('last_1c_orders_export_date', date("Y-m-d H:i:s"));
        }


        if (Request::get('type') == 'catalog' && Request::get('mode') == 'checkauth') {
            print "success\n";
            print session_name() . "\n";
            print session_id();
        }

        if (Request::get('type') == 'catalog' && Request::get('mode') == 'init') {
            $tmp_files = glob($dir . '*.*');
            if (is_array($tmp_files)) {
                foreach ($tmp_files as $v) {
                    unlink($v);
                }
            }
            Request::deleteSession('last_1c_imported_variant_num');
            Request::deleteSession('last_1c_imported_product_num');
            Request::deleteSession('features_mapping');
            Request::deleteSession('categories_mapping');
            Request::deleteSession('brand_id_option');
            print "zip=no\n";
            print "file_limit=1000000\n";
        }

        if (Request::get('type') == 'catalog' && Request::get('mode') == 'file') {
            $filename = basename(Request::get('filename'));
            $f = fopen($dir . $filename, 'ab');
            fwrite($f, file_get_contents('php://input'));
            fclose($f);
            print "success\n";
        }

        if (Request::get('type') == 'catalog' && Request::get('mode') == 'import') {
            $filename = basename(Request::get('filename'));

            if ($filename === 'import.xml') {

                // Категории и свойства (только в первом запросе пакетной передачи)
                if (empty(Request::getSession('last_1c_imported_product_num'))) {
                    $z = new \XMLReader();
                    $z->open($dir . $filename);
                    while ($z->read() && $z->name !== 'Классификатор');
                    $xml = new \SimpleXMLElement($z->readOuterXML());
                    $z->close();
                    $this->import_categories($xml);
                    $this->import_features($xml);
                }

                // Товары
                $z = new \XMLReader();
                $z->open($dir . $filename);

                while ($z->read() && $z->name !== 'Товар');

                // Последний товар, на котором остановились
                $last_product_num = 0;
                if (!empty(Request::getSession('last_1c_imported_product_num'))) {
                    $last_product_num = Request::getSession('last_1c_imported_product_num');
                }

                // Номер текущего товара
                $current_product_num = 0;

                while ($z->name === 'Товар') {
                    if ($current_product_num >= $last_product_num) {
                        $xml = new \SimpleXMLElement($z->readOuterXML());

                        // Товары
                        $this->import_product($xml);

                        $exec_time = microtime(true) - $start_time;
                        if ($exec_time + 1 >= $max_exec_time) {
                            header("Content-type: text/xml; charset=utf-8");
                            print "\xEF\xBB\xBF";
                            print "progress\r\n";
                            print "Выгружено товаров: $current_product_num\r\n";
                            Request::setSession('last_1c_imported_product_num', $current_product_num);
                            exit();
                        }
                    }
                    $z->next('Товар');
                    $current_product_num++;
                }
                $z->close();
                print "success";
                //unlink($dir.$filename);
                Request::deleteSession('last_1c_imported_product_num');
            } elseif ($filename === 'offers.xml') {

                // Варианты
                $z = new \XMLReader();
                $z->open($dir . $filename);

                while ($z->read() && $z->name !== 'Предложение');

                // Последний вариант, на котором остановились
                $last_variant_num = 0;
                if (!empty(Request::getSession('last_1c_imported_variant_num'))) {
                    $last_variant_num = Request::getSession('last_1c_imported_variant_num');
                }

                // Номер текущего товара
                $current_variant_num = 0;

                while ($z->name === 'Предложение') {
                    if ($current_variant_num >= $last_variant_num) {
                        $xml = new \SimpleXMLElement($z->readOuterXML());

                        // Варианты
                        $this->import_variant($xml);

                        $exec_time = microtime(true) - $start_time;
                        if ($exec_time + 1 >= $max_exec_time) {
                            header("Content-type: text/xml; charset=utf-8");
                            print "\xEF\xBB\xBF";
                            print "progress\r\n";
                            print "Выгружено ценовых предложений: $current_variant_num\r\n";
                            Request::setSession('last_1c_imported_variant_num', $current_variant_num);
                            exit();
                        }
                    }
                    $z->next('Предложение');
                    $current_variant_num++;
                }
                $z->close();
                print "success";

                //unlink($dir.$filename);
                Request::deleteSession('last_1c_imported_variant_num');
            }
        }
    }


    public function import_categories($xml, $parent_id = 0)
    {
        global $dir;

        if (isset($xml->Группы->Группа)) {
            foreach ($xml->Группы->Группа as $xml_group) {
                $category_id = DatabaseQuery::query('SELECT id FROM __product_category WHERE external_id=?', $xml_group->Ид)->result('id');
                if (empty($category_id)) {
                    $category_id = ProductCategory::addCategory([
                        'parent_id' => $parent_id,
                        'external_id' => $xml_group->Ид,
                        'name' => $xml_group->Наименование,
                        'meta_title' => $xml_group->Наименование,
                        'meta_description' => $xml_group->Наименование
                    ]);
                }
                Request::setSession('categories_mapping', [strval($xml_group->Ид), $category_id]);
                $this->import_categories($xml_group, $category_id);
            }
        }
    }


    public function import_features($xml)
    {
        global $brand_option_name;

        $property = array();
        if (isset($xml->Свойства->СвойствоНоменклатуры)) {
            $property = $xml->Свойства->СвойствоНоменклатуры;
        }

        if (isset($xml->Свойства->Свойство)) {
            $property = $xml->Свойства->Свойство;
        }

        foreach ($property as $xml_feature) {

            // Если свойство содержит производителя товаров
            if ($xml_feature->Наименование == $brand_option_name) {

                // Запомним в сессии Ид свойства с производителем
                Request::setSession('brand_option_id', strval($xml_feature->Ид));
            }

            // Иначе обрабатываем как обычной свойство товара
            else {
                $feature = ProductFeature::where('name', strval($xml_feature->Наименование))->get();

                if (empty($feature->id)) {
                    $feature = ProductFeature::create(['name' => strval($xml_feature->Наименование)]);
                }

                Request::setSession('features_mapping', [strval($xml_feature->Ид), $feature->id]);
                if ($xml_feature->ТипЗначений == 'Справочник') {
                    foreach ($xml_feature->ВариантыЗначений->Справочник as $val) {
                        Request::setSession('features_values', [strval($val->ИдЗначения), strval($val->Значение)]);
                    }
                }
            }
        }
    }


    public function import_product($xml_product)
    {
        global $dir;
        global $full_update;


        // Товары
        //  Id товара и варианта (если есть) по 1С
        @list($product_1c_id, $variant_1c_id) = explode('#', $xml_product->Ид);
        if (empty($variant_1c_id)) {
            $variant_1c_id = '';
        }

        // Ид категории
        if (isset($xml_product->Группы->Ид)) {
            $category_id = Request::getSession('categories_mapping')[strval($xml_product->Группы->Ид)];
        }


        // Подгатавливаем вариант
        $variant_id = null;
        $variant = new \stdClass();
        $values = array();
        if (isset($xml_product->ХарактеристикиТовара->ХарактеристикаТовара)) {
            foreach ($xml_product->ХарактеристикиТовара->ХарактеристикаТовара as $xml_property) {
                $values[] = $xml_property->Значение;
            }
        }
        if (!empty($values)) {
            $variant->name = join(', ', $values);
        }
        $variant->sku = (string)$xml_product->Артикул;
        $variant->external_id = $variant_1c_id;

        // Ищем товар
        $product = Product::where('external_id', $product_1c_id)->get();
        if (empty($product->id) && !empty($variant->sku)) {
            $res = DatabaseQuery::query('SELECT product_id, id FROM __product_variant WHERE sku=?', $variant->sku)->result();
            if (!empty($res)) {
                $product->id = $res->product_id;
                $variant_id = $res->id;
            }
        }

        // Если такого товара не нашлось
        if (empty($product->id)) {

            // Добавляем товар
            $description = '';
            if (!empty($xml_product->Описание)) {
                $description = $xml_product->Описание;
            }

            $product = Product::addProduct([
                'external_id' => $product_1c_id,
                'url' => Helper::slugEn($xml_product->Наименование),
                'name' => $xml_product->Наименование,
                'meta_title' => $xml_product->Наименование,
                'meta_description' => $xml_product->$description,
                'annotation' => $description,
                'body' => $description
            ]);

            // Добавляем товар в категории
            if (isset($category_id)) {
                Product::updateProduct($product->id, ['category_id' => $category_id]);
            }

            // Добавляем изображение товара
            if (isset($xml_product->Картинка)) {
                foreach ($xml_product->Картинка as $img) {
                    $image = basename($xml_product->Картинка);
                    if (!empty($image) && is_file($dir . $image) && is_writable(Config::get('images_originals_dir'))) {
                        rename($dir . $image, Config::get('images_originals_dir') . $image);
                        Image::addImage($product->id, 'product', $image);
                    }
                }
            }
        }

        // Если нашелся товар
        else {
            if (empty($variant_id) && !empty($variant_1c_id)) {
                $variant_id = DatabaseQuery::query('SELECT id FROM __product_variant WHERE external_id=? AND product_id=?', $variant_1c_id, $product->id)->result('id');
            } elseif (empty($variant_id) && empty($variant_1c_id)) {
                $variant_id = DatabaseQuery::query('SELECT id FROM __product_variant WHERE product_id=?', $product->id)->result('id');
            }

            // Обновляем товар
            if ($full_update) {
                $p = new \stdClass();
                if (!empty($xml_product->Описание)) {
                    $description = strval($xml_product->Описание);
                    $p->meta_description = $description;
                    $p->meta_description = $description;
                    $p->annotation = $description;
                    $p->body = $description;
                }
                $p->external_id = $product_1c_id;
                $p->url = Helper::slugEn($xml_product->Наименование);
                $p->name = $xml_product->Наименование;
                $p->meta_title = $xml_product->Наименование;

                Product::updateProduct($product->id, $p);

                // Обновляем категорию товара
                if (isset($category_id) && !empty($product_id)) {
                    DatabaseQuery::query('DELETE FROM __product_category WHERE product_id=?', $product->id);
                    Product::updateProduct($product_id, ['category_id' => $category_id]);
                }
            }

            // Обновляем изображение товара
            if (isset($xml_product->Картинка)) {
                foreach ($xml_product->Картинка as $img) {
                    $image = basename($img);
                    if (!empty($image) && is_file($dir . $image) && is_writable(Config::get('images_originals_dir'))) {
                        $img_id = DatabaseQuery::query('SELECT id FROM __content_image WHERE product_id=? ORDER BY position LIMIT 1', $product->id)->result('id');
                        if (!empty($img_id)) {
                            Image::deleteImage($img_id);
                        }
                        rename($dir . $image, Config::get('images_originals_dir') . $image);
                        Image::addImage($product->id, 'product', $image);
                    }
                }
            }
        }

        // Если не найден вариант, добавляем вариант один к товару
        if (empty($variant_id)) {
            $variant->product_id = $product->id;
            $variant->stock = 0;
            $variant_id = ProductVariant::addVariant($variant);
        } elseif (!empty($variant_id)) {
            ProductVariant::updateVariant($variant_id, $variant);
        }
        // Свойства товара
        if (isset($xml_product->ЗначенияСвойств->ЗначенияСвойства)) {
            foreach ($xml_product->ЗначенияСвойств->ЗначенияСвойства as $xml_option) {
                if (!empty(Request::getSession('features_mapping')[strval($xml_option->Ид)])) {
                    $feature_id = Request::getSession('features_mapping')[strval($xml_option->Ид)];
                    if (isset($category_id) && !empty($feature_id)) {
                        ProductCategoryFeature::addFeatureCategory($feature_id, $category_id);
                        $values = array();
                        foreach ($xml_option->Значение as $xml_value) {
                            if (!empty(Request::getSession('features_values')[strval($xml_value)])) {
                                $values[] = strval(Request::getSession('features_values')[strval($xml_value)]);
                            } else {
                                $values[] = strval($xml_value);
                            }
                        }
                        ProductOption::updateOption($product->id, $feature_id, join(' ,', $values));
                    }
                }
                // Если свойство оказалось названием бренда
                elseif (!empty(Request::getSession('brand_option_id')) && !empty($xml_option->Значение)) {
                    $brand_name = strval($xml_option->Значение);

                    // Добавим бренд
                    // Найдем его по имени
                    if (!$brand_id = DatabaseQuery::query('SELECT id FROM __product_brand WHERE name=?', $brand_name)->result('id')) {

                        // Создадим, если не найден
                        $brand_id = ProductBrand::addBrand(['name' => $brand_name, 'meta_title' => $brand_name, 'meta_description' => $brand_name]);
                    }
                    if (!empty($brand_id)) {
                        Product::updateProduct($product->id, ['brand_id' => $brand_id]);
                    }
                }
            }
        }


        // Если нужно - удаляем вариант или весь товар
        if ($xml_product->Статус == 'Удален') {
            ProductVariant::deleteVariant($variant_id);
            if (DatabaseQuery::query('SELECT count(id) as variants_num FROM __product_variant WHERE product_id=?', $product->id)->result('variants_num') == 0) {
                Product::deleteProduct($product->id);
            }
        }
    }


    public function import_variant($xml_variant)
    {
        global $dir;

        $variant = new \stdClass();

        //  Id товара и варианта (если есть) по 1С
        @list($product_1c_id, $variant_1c_id) = explode('#', $xml_variant->Ид);
        if (empty($variant_1c_id)) {
            $variant_1c_id = '';
        }
        if (empty($product_1c_id)) {
            return false;
        }

        $variant_id = DatabaseQuery::query('SELECT v.id FROM __product_variant v WHERE v.external_id=? AND product_id=(SELECT p.id FROM __product p WHERE p.external_id=? LIMIT 1)', $variant_1c_id, $product_1c_id)->result('id');
        $variant->external_id = $variant_1c_id;
        $variant->product_id = DatabaseQuery::query('SELECT p.id FROM __product p WHERE p.external_id=?', $product_1c_id)->result('id');
        if (empty($variant->product_id)) {
            return false;
        }

        $variant->price = $xml_variant->Цены->Цена->ЦенаЗаЕдиницу;

        if (isset($xml_variant->ХарактеристикиТовара->ХарактеристикаТовара)) {
            foreach ($xml_variant->ХарактеристикиТовара->ХарактеристикаТовара as $xml_property) {
                $values[] = $xml_property->Значение;
            }
        }
        if (!empty($values)) {
            $variant->name = join(', ', $values);
        }
        $sku = (string)$xml_variant->Артикул;
        if (!empty($sku)) {
            $variant->sku = $sku;
        }


        // Конвертируем цену из валюты 1С в базовую валюту магазина
        if (!empty($xml_variant->Цены->Цена->Валюта)) {

            // Ищем валюту по коду
            $variant_currency = DatabaseQuery::query("SELECT id, rate_from, rate_to FROM __finance_currency WHERE code like ?", $xml_variant->Цены->Цена->Валюта)->result();

            // Если не нашли - ищем по обозначению
            if (empty($variant_currency)) {
                $variant_currency = DatabaseQuery::query("SELECT id, rate_from, rate_to FROM __finance_currency WHERE sign like ?", $xml_variant->Цены->Цена->Валюта)->result();
            }
            // Если нашли валюту - конвертируем из нее в базовую
            if ($variant_currency && $variant_currency->rate_from > 0 && $variant_currency->rate_to > 0) {
                $variant->price = floatval($variant->price) * $variant_currency->rate_to / $variant_currency->rate_from;
            }
        }

        $variant->stock = $xml_variant->Количество;

        if (empty($variant_id)) {
            ProductVariant::addVariant($variant);
        } else {
            ProductVariant::updateVariant($variant_id, $variant);
        }
    }
}
