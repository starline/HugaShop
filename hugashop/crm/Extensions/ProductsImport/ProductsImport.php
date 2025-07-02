<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.5
 *
 * Import products to warehouse from CSV file
 */

namespace HugaShop\Extensions\ProductsImport;

use HugaShop\Services\Config;
use HugaShop\Services\Design;
use HugaShop\Services\Helper;
use HugaShop\Services\Request;
use HugaShop\Extensions\BaseExtension;
use HugaShop\Models\Warehouse\WarehousePlace;
use Symfony\Component\HttpFoundation\JsonResponse;
use HugaShop\Extensions\ProductsImport\Services\CsvReader;
use HugaShop\Extensions\ProductsImport\Services\ProductImport;

final class ProductsImport extends BaseExtension
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
     * Import products. Ajax
     */
    public function import()
    {

        setlocale(LC_ALL, $this->locale);

        $place_id = Request::getInt('place_id');
        if (!WarehousePlace::find($place_id)) {
            return new JsonResponse(['error' => 'place'], 400);
        }

        $import_file_path = Config::get('import_files_dir') . $this->import_file;
        $csv = new CsvReader($import_file_path, $this->column_delimiter);

        $columns = $csv->getHeader();
        foreach ($columns as &$column) {
            if ($internal = ProductImport::internalColumnName($column, $this->columns_names)) {
                $column = $internal;
            }
        }

        if (!in_array('sku', $columns)) {
            return new JsonResponse(['error' => 'columns'], 400);
        }

        if ($from = Request::getInt('from')) {
            $csv->seekTo($from);
        }

        $imported_items = [];
        foreach ($csv->readRows($this->products_count) as $line) {
            $csv_product = [];
            foreach ($columns as $num => $name) {
                $csv_product[$name] = $line[$num] ?? null;
            }

            foreach (ProductImport::importItem($csv_product, $place_id) as $it) {
                $imported_items[] = $it;
            }
        }

        $result = new \stdClass();
        $result->end = $csv->eof();
        $result->from = $csv->tell();
        $result->file_size = $csv->fileSize();
        $result->num = $num = Request::getInt('num') + count($imported_items);

        Design::assign('num', $num);
        Design::assign('items', $imported_items);
        Design::assign('ajax_block', 'imported_products');

        $content_tpl = $this->getTemplatePath('templates/import_part.tpl');
        $result->items = Design::fetch($content_tpl);

        // CsvReader uses SplFileObject which doesn't require explicit closing

        if (empty(Request::getInt('from'))) {
            $result->file_size_h = Helper::convertBytes($csv->fileSize());
            $result->file_rows = $csv->countRows();
        }

        return $result;
    }
}
