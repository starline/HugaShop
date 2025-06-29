<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.1
 *
 * AJAX CSV import to warehouse
 */

namespace App\Controller\Admin\Ajax;

use HugaShop\Models\Config;
use HugaShop\Models\Helper;
use HugaShop\Models\Design;
use HugaShop\Models\Request;
use HugaShop\Models\Product\Product;
use HugaShop\Models\Warehouse\WarehousePlace;
use HugaShop\Models\Warehouse\WarehouseProduct;
use App\Controller\BaseAdminController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

class WarehouseImportAjax extends BaseAdminController
{
    private array $columns_names = [
        'sku'        => ['sku', 'артикул'],
        'amount'     => ['amount', 'qty', 'количество', 'кол-во'],
        'price'      => ['price', 'цена'],
        'cost_price' => ['cost price', 'оптовая цена']
    ];

    private string $import_file = 'wh_import.csv';
    private string $column_delimiter = ',';
    private int $products_count = 20;
    private array $columns = [];

    #[Route('/admin/ajax/warehouse_import', name: 'WarehouseImportAjaxAdmin')]
    public function import()
    {
        $this->checkAdminAccess('warehouse');
        setlocale(LC_ALL, 'ru_RU.UTF-8');

        $place_id = Request::getInt('place_id');
        if (!WarehousePlace::find($place_id)) {
            return new JsonResponse(['error' => 'place'], 400);
        }

        $import_file_path = Config::get('import_files_dir') . $this->import_file;
        $file = fopen($import_file_path, 'r');
        $this->columns = fgetcsv($file, null, $this->column_delimiter);
        foreach ($this->columns as &$column) {
            if ($internal = $this->internalColumnName($column, $this->columns_names)) {
                $column = $internal;
            }
        }
        if (!in_array('sku', $this->columns) || !in_array('amount', $this->columns)) {
            return new JsonResponse(['error' => 'columns'], 400);
        }
        if ($from = Request::getInt('from')) {
            fseek($file, $from);
        }

        $imported_items = [];
        for ($k = 0; !feof($file) && $k < $this->products_count; $k++) {
            $line = fgetcsv($file, 0, $this->column_delimiter);
            if (is_array($line)) {
                $csv_product = [];
                foreach ($this->columns as $num => $name) {
                    $csv_product[$name] = $line[$num] ?? null;
                }
                $items = $this->importItem($csv_product, $place_id);
                foreach ($items as $it) {
                    $imported_items[] = $it;
                }
            }
        }

        $result = new \stdClass();
        $result->end = feof($file);
        $result->from = ftell($file);
        $result->file_size = filesize($import_file_path);
        $result->num = $num = Request::getInt('num') + count($imported_items);

        Design::assign('num', $num);
        Design::assign('items', $imported_items);

        $tpl = Config::get('extension_dir') . 'WarehouseImport/templates/index.tpl';
        $result->items = $this->fetch($tpl, 'imported_products');

        fclose($file);

        if (empty(Request::getInt('from'))) {
            $result->file_size_h = Helper::convertBytes(filesize($import_file_path));
            $result->file_rows = count(file($import_file_path));
        }

        return new JsonResponse($result);
    }

    private function importItem(array $item, int $place_id): array
    {
        if (empty($item['sku']) || empty($item['amount'])) {
            return [];
        }
        foreach (['price', 'cost_price', 'amount'] as $k) {
            if (isset($item[$k])) {
                $item[$k] = str_replace(['\xc2\xa0', ','], ['', '.'], strval($item[$k]));
            }
        }
        $products = Product::getList(['sku' => $item['sku']]);
        $result = [];
        foreach ($products as $product) {
            $amount = (int) $item['amount'];
            Product::changeAmount($product->id, $amount);
            WarehouseProduct::changeAmount($product->id, $place_id, $amount);
            $it = new \stdClass();
            $it->product = Product::getOne($product->id);
            $it->amount = $amount;
            $it->status = 'added';
            $result[] = $it;
        }
        if ($products->isEmpty()) {
            $err = new \stdClass();
            $err->error = 'Product not found: ' . $item['sku'];
            $result[] = $err;
        }
        return $result;
    }

    private function internalColumnName(string $name, array $columns_names)
    {
        $name = trim(str_replace(['/', '\\'], '', $name));
        foreach ($columns_names as $i => $names) {
            foreach ($names as $n) {
                if ($name !== '' && preg_match("/^" . preg_quote($name) . "$/ui", $n)) {
                    return $i;
                }
            }
        }
        return false;
    }
}
