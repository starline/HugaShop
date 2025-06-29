<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.2
 *
 * Import products to warehouse from CSV file
 */

namespace HugaShop\Extensions\WarehouseImport;

use HugaShop\Models\Config;
use HugaShop\Models\Design;
use HugaShop\Models\Helper;
use HugaShop\Models\Request;

use HugaShop\Extensions\BaseExtension;
use HugaShop\Models\Warehouse\WarehousePlace;

use Symfony\Component\HttpFoundation\JsonResponse;
use HugaShop\Extensions\WarehouseImport\Services\ProductImport;

final class WarehouseImport extends BaseExtension
{
    private array $columns_names = [
        'sku'        => ['sku', 'артикул', 'Арт'],
        'amount'     => ['amount', 'qty', 'количество', 'кол-во'],
        'price'      => ['price', 'цена'],
        'cost_price' => ['cost price', 'оптовая цена']
    ];

    private string $import_file = 'wh_import.csv';
    private string $column_delimiter = ',';

    private int $products_count = 20;

    private string $locale = 'ru_RU.UTF-8';

    /**
     * Show import page and handle file upload
     */
    public function index()
    {
        Design::assign('import_files_dir', Config::get('import_files_dir'));
        Design::assign('places', WarehousePlace::getList(order: 'position'));

        if (!is_writable(Config::get('import_files_dir'))) {
            Design::assign('message_error', 'no_permission');
            return $this->getTemplatePath('templates/index.tpl');
        }

        if (Request::checkCSRF() && Request::files('file')) {
            $place_id = Request::postInt('place_id');

            if ($place_id) {
                $uploaded_name = Request::files('file', 'tmp_name');
                $temp_name = tempnam(Config::get('import_files_dir'), 'temp_');

                if (move_uploaded_file($uploaded_name, $temp_name)) {
                    $dest = Config::get('import_files_dir') . $this->import_file;

                    if (ProductImport::convertFile($temp_name, $dest)) {
                        Design::assign('place_id', $place_id);
                        Design::assign('filename', Request::files('file', 'name'));
                    } else {
                        Design::assign('message_error', 'convert_error');
                    }
                } else {
                    Design::assign('message_error', 'upload_error');
                }

                unlink($temp_name);
            } else {
                Design::assign('message_error', 'place_error');
            }
        }

        return $this->getTemplatePath('templates/index.tpl');
    }


    /**
     * Import products
     */
    public function import()
    {

        setlocale(LC_ALL, $this->locale);

        $place_id = Request::getInt('place_id');
        if (!WarehousePlace::find($place_id)) {
            return new JsonResponse(['error' => 'place'], 400);
        }

        $import_file_path = Config::get('import_files_dir') . $this->import_file;
        $file = fopen($import_file_path, 'r');

        $columns = fgetcsv($file, null, $this->column_delimiter);
        foreach ($columns as &$column) {
            if ($internal = ProductImport::internalColumnName($column, $this->columns_names)) {
                $column = $internal;
            }
        }

        if (!in_array('sku', $columns)) {
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
                foreach ($columns as $num => $name) {
                    $csv_product[$name] = $line[$num] ?? null;
                }

                $items = ProductImport::importItem($csv_product, $place_id);
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
        Design::assign('ajax_block', 'imported_products');

        $content_tpl = $this->getTemplatePath('templates/import_part.tpl');
        $result->items = Design::fetch($content_tpl);

        fclose($file);

        if (empty(Request::getInt('from'))) {
            $result->file_size_h = Helper::convertBytes(filesize($import_file_path));
            $result->file_rows = count(file($import_file_path));
        }

        return $result;
    }
}
