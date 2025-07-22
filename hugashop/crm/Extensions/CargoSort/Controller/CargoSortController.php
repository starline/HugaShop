<?php


/**
 * Упаковке в контейнеры
 * Алгоритм №1 - Сначало самые большие шины ложим и заполняем контейнер поменьше 
 *
 * @author Andrey Guzhva
 * 
 */

// Устанавливаем максимальное время выполнения скрипта
set_time_limit(60 * 0.5); // 60 * N минут 

require_once('Auth.php');

class CargoSortAdmin extends BaseAdminController
{

    public $import_files_dir = 'files/imports/';
    public $import_file = 'cargosort.csv';
    public $import_columns = array('tyre_size', 'name', 'size', 'cost', 'count', 'firstly_count');

    private $export_files_dir = 'files/exports/';
    private $export_filename = 'cargosort.csv';



    private $allowed_extensions = array('csv', 'txt');
    private $locale = 'ru_RU.UTF-8';
    private $column_delimiter      = ';';

    private $total_size = 0;
    private $total_cost = 0;
    private $box_size = 0;
    private $box_cost = 0;

    
    #[Route('/CargoSort', name: 'ExtCargoSort', priority: 20)]
    public function fetch()
    {

        $this->design->assign('import_files_dir', $this->import_files_dir);

        if (!is_writable($this->import_files_dir))
            $this->design->assign('message_error', 'no_permission');

        // Проверяем локаль
        $old_locale = setlocale(LC_ALL, 0);
        setlocale(LC_ALL, $this->locale);

        if (setlocale(LC_ALL, 0) != $this->locale) {
            $this->design->assign('message_error', 'locale_error');
            $this->design->assign('locale', $this->locale);
        }
        setlocale(LC_ALL, $old_locale);


        if ($this->request->method('post') && $this->request->files("file")) {

            $this->box_size = $this->request->post('box_size');
            $this->box_cost = $this->request->post('box_cost');
            $this->box_delivery_cost = $this->request->post('box_delivery_cost');

            if ($this->box_size > 0 and $this->box_cost > 0) {

                $uploaded_name = $this->request->files("file", "tmp_name");
                $temp = tempnam($this->import_files_dir, 'temp_');

                if (!move_uploaded_file($uploaded_name, $temp))
                    $this->design->assign('message_error', 'upload_error');

                if (!$this->convert_file($temp, $this->import_files_dir . $this->import_file))
                    $this->design->assign('message_error', 'convert_error');
                else
                    $this->design->assign('filename',  $this->request->files("file", "name"));

                unlink($temp);

                // Разбираем файл
                $f = fopen($this->import_files_dir . $this->import_file, 'r');

                $products = array();

                // Проходимся по строкам, пока не конец файла
                while (($line = fgetcsv($f, 0, $this->column_delimiter)) !== FALSE) {

                    $product = null;

                    if (is_array($line)) {

                        // Проходимся по колонкам строки
                        foreach ($this->import_columns as $i => $col) {

                            // Создаем массив item[название_колонки]=значение
                            if (isset($line[$i]) && !empty($line) && !empty($col))
                                $product[$col] = $line[$i];
                        }

                        // Заменяем запятую на точку, в весе и цене
                        $product['size'] =  (float)str_replace(',', '.', trim($product['size']));
                        $product['cost'] =  (float)str_replace(',', '.', trim($product['cost']));

                        // Сколько товаров в первую очередь
                        if (isset($product['firstly_count']))
                            $temp_firstly_count = $product['firstly_count'];

                        // Создаем товар по кол-ву
                        for ($i = 0; $i < $product['count']; $i++) {

                            $product['firstly'] = 2;

                            // Отмечаем товары, что в первую очередь
                            if (!empty($temp_firstly_count) and $temp_firstly_count > 0) {
                                $temp_firstly_count--;
                                $product['firstly'] = 1;
                            }

                            // собираем массив
                            $products[] = $product;

                            // Считаем общий вес, стоимость груза
                            $this->total_size += $product['size'];
                            $this->total_cost += $product['cost'];
                        }
                    }
                }

                // Идеальное кол-во контейнеров
                $ideal_box_count_of_size = $this->total_size / $this->box_size;
                $ideal_box_count_of_cost = $this->total_cost / $this->box_cost;

                // сортируем сначало по порядку,  потом по весу
                $temp_size = array();
                $temp_firstly = array();
                foreach ($products as $k => $p) {
                    $temp_size[$k] = $p["size"];
                    $temp_firstly[$k] = $p['firstly'];
                }

                array_multisort($temp_firstly, SORT_ASC, SORT_NUMERIC, $temp_size, SORT_DESC, SORT_NUMERIC, $products);


                // Удалим старый файл экспорта
                if (is_writable($this->export_files_dir . $this->export_filename))
                    unlink($this->export_files_dir . $this->export_filename);

                // Открываем файл экспорта на добавление
                $export_f = fopen($this->export_files_dir . $this->export_filename, 'ab');


                // Собираем контейнеры
                $boxes_array = array();
                $number_of_box = 1;
                $sort_products = array();
                $temp_products_arr = $products;

                while (count($temp_products_arr) > 0) {

                    $temp_size = 0;
                    $temp_cost = 0;

                    $box_array = array();

                    foreach ($temp_products_arr as $n => $p) {

                        // Если цена товара больше стоимости контейнера, прерываем и выдаем предупреждение
                        if ($p['cost'] > $this->box_cost) {
                            return $this->design->assign('message_error', 'Цена товара больше стоимости контейнера');
                        }

                        // Cобираем один контейнер
                        if (($temp_size + $p['size']) < $this->box_size and ($temp_cost + $p['cost']) < $this->box_cost) {

                            $temp_size += $p['size'];
                            $temp_cost += $p['cost'];

                            $p['number_of_box'] = $number_of_box;

                            unset($p['count']);
                            unset($p['firstly_count']);

                            $sort_products[] = $p;
                            end($sort_products);
                            $box_array[key($sort_products)] =  $p;

                            // Заменяем точки на запятую для CSV
                            $p['size'] =  (string)str_replace('.', ',', $p['size']);
                            $p['cost'] =  (string)str_replace('.', ',', $p['cost']);

                            fputcsv($export_f, $p, $this->column_delimiter);


                            // Удаляем товар из общего списка
                            unset($temp_products_arr[$n]);
                        }
                    }

                    // Если есть значение стоимости доставки контейнера,
                    // производим вычисления стоиомсти доставки одного товара в контейнере
                    if (isset($this->box_delivery_cost)) {

                        // Кол-во товаров в контейнере
                        $product_in_box_count = count($box_array);

                        // Находим стоимость доставки одного товара в контейнере
                        $delivery_product_in_box_cost = $this->box_delivery_cost / $product_in_box_count;

                        // Вносим данные в $sort_products
                        foreach ($box_array as $key => $value) {
                            $sort_products[$key]['delivery_product_in_box_cost'] = $delivery_product_in_box_cost;
                        }
                    }

                    $boxes_array[$number_of_box] = $box_array;
                    $number_of_box++;
                }

                // Если есть значение стоимости доставки контейнера, производим вычисления 
                // средней стоимости доставки
                if (isset($this->box_delivery_cost)) {
                    // Вычисляем среднюю цену доставки артикула
                    $temp_products_arr = $sort_products;

                    while (count($temp_products_arr) > 0) {

                        $article_array = array();

                        $temp_product = array_shift($temp_products_arr);
                        $article_array[] = $temp_product;
                        $all_delivery_cost = $temp_product['delivery_product_in_box_cost'];

                        foreach ($temp_products_arr as $key => $p) {

                            if ($p['name'] == $temp_product['name']) {
                                $article_array[] = $p;

                                $all_delivery_cost += $p['delivery_product_in_box_cost'];

                                // Удаляем товар из списка
                                unset($temp_products_arr[$key]);
                            }
                        }

                        // Средняя цена доставки артикула
                        $middle_delivery_cost = $all_delivery_cost / count($article_array);

                        // Вносим значения в $sort_products
                        foreach ($sort_products as $key => $p) {
                            if ($p['name'] == $temp_product['name']) {
                                $sort_products[$key]['middle_delivery_cost'] = $middle_delivery_cost;
                            }
                        }
                    }
                }

                fclose($export_f);

                $this->design->assign('box_count', $number_of_box - 1);
                $this->design->assign('products', $sort_products);

                $this->design->assign('total_size', $this->total_size);
                $this->design->assign('total_cost', $this->total_cost);

                $this->design->assign('ideal_box_count_of_size', $ideal_box_count_of_size);
                $this->design->assign('ideal_box_count_of_cost', $ideal_box_count_of_cost);

                $this->design->assign('box_size', $this->box_size);
                $this->design->assign('box_cost', $this->box_cost);
                $this->design->assign('box_delivery_cost', $this->box_delivery_cost);
            } else {
                $this->design->assign('message_error', 'Установите размер и стоимоть груза контейнера');
            }
        }

        return $this->design->fetch('cargosort.tpl');
    }



    private function convert_file($source, $dest)
    {

        // Узнаем какая кодировка у файла
        $teststring = file_get_contents($source, null, null, null, 1000000);

        if (preg_match('//u', $teststring)) { // Кодировка - UTF8
            // Просто копируем файл
            return copy($source, $dest);
        } else {

            // Конвертируем в UFT8
            if (!$src = fopen($source, "r"))
                return false;

            if (!$dst = fopen($dest, "w"))
                return false;

            while (($line = fgets($src, 4096)) !== false) {
                $line = $this->win_to_utf($line);
                fwrite($dst, $line);
            }

            fclose($src);
            fclose($dst);
            return true;
        }
    }



    private function win_to_utf($text)
    {

        if (function_exists('iconv')) {
            return @iconv('windows-1251', 'UTF-8', $text);
        } else {
            $t = '';
            for ($i = 0, $m = strlen($text); $i < $m; $i++) {
                $c = ord($text[$i]);
                if ($c <= 127) {
                    $t .= chr($c);
                    continue;
                }
                if ($c >= 192 && $c <= 207) {
                    $t .= chr(208) . chr($c - 48);
                    continue;
                }
                if ($c >= 208 && $c <= 239) {
                    $t .= chr(208) . chr($c - 48);
                    continue;
                }
                if ($c >= 240 && $c <= 255) {
                    $t .= chr(209) . chr($c - 112);
                    continue;
                }
                if ($c == 184) {
                    $t .= chr(209) . chr(145);
                    continue;
                } #ё
                if ($c == 168) {
                    $t .= chr(208) . chr(129);
                    continue;
                } #Ё
                if ($c == 179) {
                    $t .= chr(209) . chr(150);
                    continue;
                } #і
                if ($c == 178) {
                    $t .= chr(208) . chr(134);
                    continue;
                } #І
                if ($c == 191) {
                    $t .= chr(209) . chr(151);
                    continue;
                } #ї
                if ($c == 175) {
                    $t .= chr(208) . chr(135);
                    continue;
                } #ї
                if ($c == 186) {
                    $t .= chr(209) . chr(148);
                    continue;
                } #є
                if ($c == 170) {
                    $t .= chr(208) . chr(132);
                    continue;
                } #Є
                if ($c == 180) {
                    $t .= chr(210) . chr(145);
                    continue;
                } #ґ
                if ($c == 165) {
                    $t .= chr(210) . chr(144);
                    continue;
                } #Ґ
                if ($c == 184) {
                    $t .= chr(209) . chr(145);
                    continue;
                }; #Ґ            
            }
            return $t;
        }
    }
}
