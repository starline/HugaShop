<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.9
 * 
 * Import gooogle doc csv
 *
 */

namespace App\Controller\Admin\Ajax;

use HugaShop\Api\Config;
use HugaShop\Api\Helper;
use HugaShop\Api\Product\Product;
use HugaShop\Api\Request;
use HugaShop\Api\Product\ProductVariant;
use HugaShop\Api\Finance\FinanceCurrency;
use App\Controller\BaseAdminController;
use HugaShop\Api\Design;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

class ImportCsvAjax extends BaseAdminController
{
    // Соответствие полей в базе и номера колоноки в файле
    private $columns_names = [
        'name' =>                   ['product', 'name', 'товар', 'название', 'наименование'],
        'price' =>                  ['price', 'цена', 'на продажу, грн', 'на продажу, руб'],
        'cost_price' =>             ['cost price', 'оптовая цена', 'оптовая цена, грн', 'оптовая цена, руб'],
        'sku' =>                    ['sku', 'артикул', 'арт'],
        'weight' =>                 ['weight', 'вес', 'вес, кг', 'масса', 'кг']
    ];

    private $import_file              = 'gdocs_import.csv';       # Временный файл
    private $column_delimiter         = ',';                      # Разделитель колонок
    private $products_count           = 20;                       # Импортируем по N строк
    private $columns                  = [];


    #[Route('/admin/ajax/import/{type}', name: 'ImportCsvAjaxAdmin')]
    public function import(string $type)
    {

        $this->checkAdminAccess('product_import');

        // Для корректной работы установим локаль UTF-8
        setlocale(LC_ALL, 'ru_RU.UTF-8');

        $import_file_path = Config::get('import_files_dir') . $this->import_file;

        // Открывает файл только для чтения; помещает указатель в начало файла.
        $file = fopen($import_file_path, 'r');

        // Определяем колонки из первой строки файла
        $this->columns = fgetcsv($file, null, $this->column_delimiter);

        // Заменяем имена колонок из файла на внутренние имена колонок
        foreach ($this->columns as &$column) {
            if ($internal_name = $this->internalColumnName($column, $this->columns_names)) {
                $column = $internal_name;
            }
        }

        // Если нет цены и артикула - не будем импортировать
        if (!in_array('price', $this->columns) && !in_array('sku', $this->columns)) {
            return false;
        }

        // Переходим на заданную позицию, если импортируем не сначала
        if ($from = Request::get('from', 'int')) {
            fseek($file, $from);
        }

        // Массив импортированных товаров
        $imported_items = [];

        // Проходимся по строкам, пока не конец файла
        // или пока не импортировано достаточно строк для одного запроса
        for ($k = 0; !feof($file) && $k < $this->products_count; $k++) {

            // Читаем строку
            $line = fgetcsv($file, 0, $this->column_delimiter);
            $csv_product = [];
            if (is_array($line)) {

                // Проходимся по колонкам строки
                foreach ($this->columns as $num => $name) {

                    // Создаем массив product[название_колонки]=значение
                    $csv_product[$name] = $line[$num];
                }

                // Импортируем этот товар
                if (!empty($csv_product) and !empty($items = $this->importItem($csv_product))) {
                    foreach ($items as $item) {
                        $imported_items[] = $item;
                    }
                }
            }
        }

        $result             = new \stdClass();
        $result->end        = feof($file);                      # И закончили ли полностью весь файл
        $result->from       = ftell($file);                     # На каком месте остановились
        $result->file_size  = filesize($import_file_path);      # Размер всего файла
        $result->num =      $num = Request::get('num', 'int') + count($imported_items);

        Design::assign('num', $num);
        Design::assign('items', $imported_items);

        $result->items = $this->fetch('product/product_import.tpl', 'imported_products');;

        fclose($file);

        if (empty(Request::get('from', 'int'))) {
            $result->file_size_h = Helper::convertBytes(filesize($import_file_path));
            $result->file_rows = count(file($import_file_path));
        }

        return new JsonResponse($result);
    }


    /**
     * Импорт одного товара
     * $product[column_name] = value;
     * @param array $product
     */
    public function importItem(array $new_product)
    {

        // Проверим обязательные параметры
        foreach (['sku', 'price'] as $key) {
            if (empty($new_product[$key])) {
                return false;
            }
        }

        // Убираем пробелы и меняем "," на "."
        foreach (['cost_price', 'price', 'weight', 'sku'] as $key) {
            if (empty($new_product[$key])) {
                continue;
            }
            $new_product[$key] = str_replace([' ', ','], ['', '.'], strval($new_product[$key]));
        }

        $items = [];

        // Вариантов с одинаковым артикулом может быть несколько
        $prev_variants = ProductVariant::getList(['sku' => $new_product['sku']]);
        foreach ($prev_variants as $prev_variant) {

            // Подготовим вариант товара
            $variant = new \stdClass();

            // Price
            if (Request::get('no_price', 'int') != 1 and $new_product['price'] != $prev_variant->price) {
                $variant->price = $new_product['price'];

                // Вычисляем старую цену
                if ($prev_variant->price > $new_product['price']) {
                    $variant->old_price = $prev_variant->price;
                }
            }

            // Cost price
            if ($new_product['cost_price'] != $prev_variant->cost_price) {
                $variant->cost_price = $new_product['cost_price'];
            }

            // Weight
            if (!empty($new_product['weight']) and $new_product['weight'] != $prev_variant->weight) {
                $variant->weight = $new_product['weight'];
            }


            $item = new \stdClass();

            // Делаем импорт если есть изменения
            if (!empty((array) $variant)) {
                $variant->date = date("Y-m-d H:i:s"); # Определяем дату обновления. В базе 2014-11-30 21:05:08
                ProductVariant::updateVariant($prev_variant->id, $variant);
                $item->status = 'updated';
            } else {
                $item->status = 'not_updated';
            }

            $variant = ProductVariant::getOne($prev_variant->id);

            // Отобразим обновленный товар
            $item->variant =                   $variant;
            $item->prev_variant =              $prev_variant;
            $item->product =                   Product::getOne($variant->product_id);

            $items[] = $item;
        }

        return $items;
    }


    /**
     * Фозвращает внутреннее название колонки по названию колонки в файле
     * @param string $name
     */
    public function internalColumnName(string $name, $columns_names)
    {
        $name = trim($name);
        $name = str_replace('/', '', $name);
        $name = str_replace('\/', '', $name);
        foreach ($columns_names as $i => $names) {
            foreach ($names as $n) {
                if (!empty($name) && preg_match("/^" . preg_quote($name) . "$/ui", $n)) {
                    return $i;
                }
            }
        }

        return false;
    }
}
