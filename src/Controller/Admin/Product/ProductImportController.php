<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.1
 *
 * Работаем над страницей импорта товаров
 *
 */

namespace App\Controller\Admin\Product;

use HugaShop\Models\Config;
use HugaShop\Models\Design;
use HugaShop\Models\Request;
use App\Controller\BaseAdminController;
use Symfony\Component\Routing\Attribute\Route;

class ProductImportController extends BaseAdminController
{
    public $import_file = '_import.csv';
    public $allowed_extensions = ['csv', 'txt'];
    private $locale = 'ru_RU.UTF-8';
    private static $price_types = [
        'gdocs' => 'Цены на комплектующие'
    ];


    #[Route('/admin/products/import', name: 'ProductsImportPAdmin')]
    public function index()
    {

        Design::assign('import_files_dir', Config::get('import_files_dir'));
        Design::assign('price_types', self::$price_types);

        if (!is_writable(Config::get('import_files_dir'))) {
            Design::assign('message_error', 'no_permission');
        }

        // Проверяем локаль
        $old_locale = setlocale(LC_ALL, 0);
        setlocale(LC_ALL, $this->locale);
        if (setlocale(LC_ALL, 0) != $this->locale) {
            Design::assign('message_error', 'locale_error');
            Design::assign('locale', $this->locale);
        }
        setlocale(LC_ALL, $old_locale);


        if (Request::checkCSRF() && Request::files("file")) {

            $price_type = Request::post('price_type', 'string');
            Design::assign('price_type', $price_type);

            if (!empty($price_type)) {
                $uploaded_name = Request::files('file', 'tmp_name');
                $temp_name = tempnam(Config::get('import_files_dir'), 'temp_');

                if (!move_uploaded_file($uploaded_name, $temp_name)) {
                    Design::assign('message_error', 'upload_error');
                }

                if (!$this->convertFile($temp_name, Config::get('import_files_dir') . $price_type . $this->import_file)) {
                    Design::assign('message_error', 'convert_error');
                } else {
                    Design::assign('filename', Request::files('file', 'name'));
                }

                unlink($temp_name);
            } else {
                Design::assign('message_error', 'type_error');
            }
        }

        
        Design::assign('no_price', Request::post('no_price', 'int'));
        return $this->fetchResponse('product/product_import.tpl');
    }


    /**
     * Конвертируем файл
     * @param string $dest - путь к файлу
     */
    private function convertFile($source, string $dest)
    {

        // Узнаем какая кодировка у файла
        $teststring = file_get_contents($source, false, null, false, 1000000);

        if (preg_match('//u', $teststring)) { # Кодировка - UTF8

            // Просто копируем файл
            return copy($source, $dest);
        } else {

            // Конвертируем в UFT8
            if (!$src = fopen($source, "r")) {
                return false;
            }

            if (!$dst = fopen($dest, "w")) {
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
    }


    /**
     * Конвертируем win файл в UTF8
     * @param string $text - текст
     */
    private function winToUTF(string $text)
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
