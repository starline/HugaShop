<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.1
 *
 * Import products to warehouse from CSV file
 */

namespace HugaShop\Extensions\WarehouseImport;

use HugaShop\Models\Design;
use HugaShop\Models\Request;
use HugaShop\Models\Config;
use HugaShop\Models\Warehouse\WarehousePlace;
use HugaShop\Extensions\BaseExtension;

final class WarehouseImport extends BaseExtension
{
    private string $importFile = 'wh_import.csv';
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
                    $dest = Config::get('import_files_dir') . $this->importFile;
                    if ($this->convertFile($temp_name, $dest)) {
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

    private function convertFile(string $source, string $dest): bool
    {
        $teststring = file_get_contents($source, false, null, 0, 1000000);
        if (preg_match('//u', $teststring)) {
            return copy($source, $dest);
        }

        $src = fopen($source, 'r');
        $dst = fopen($dest, 'w');
        if (!$src || !$dst) {
            return false;
        }

        while (($line = fgets($src, 4096)) !== false) {
            $line = $this->winToUTF($line);
            fwrite($dst, $line);
        }

        fclose($src);
        fclose($dst);
        return true;
    }

    private function winToUTF(string $text): string
    {
        if (function_exists('iconv')) {
            return @iconv('windows-1251', 'UTF-8', $text);
        }

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
            }
            if ($c == 168) {
                $t .= chr(208) . chr(129);
                continue;
            }
        }
        return $t;
    }
}
