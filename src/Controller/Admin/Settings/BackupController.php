<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 3.9
 *
 * BackupAdmin
 *
 * Use PhpZip lib
 * @link https://github.com/Ne-Lexa/php-zip
 *
 */

namespace App\Controller\Admin\Settings;

use PhpZip\ZipFile;
use HugaShop\Services\Config;
use HugaShop\Services\Design;
use HugaShop\Services\Request;
use HugaShop\Models\Settings;
use App\Controller\BaseAdminController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Illuminate\Database\Capsule\Manager as DB;

class BackupController extends BaseAdminController
{

    #[Route('/admin/backup', name: 'BackupAdmin')]
    public function index(): Response
    {

        $this->checkAdminAccess('backup');

        set_time_limit(600);

        $backup_dir     = Config::get('root_dir') . 'public/files/backup/';
        $archive_dir    = Config::get('root_dir') . 'public/files/';

        define('PCLZIP_TEMPORARY_DIR', $backup_dir);

        // Обработка действий
        if (Request::checkCSRF()) {
            switch (Request::post('action')) {
                case 'create': { # Создаем Бэкап

                        // Определяем название файла
                        if (empty($file_name = Settings::getParam('domain'))) {
                            $file_name = Config::get('database')->name;
                        }

                        $file_path = $backup_dir . $file_name . '_' . date("Y-m-d_H-i-s") . '.zip';

                        ### Дамп базы
                        // Ложим базу в папку чтобы затем добавить файл в архив
                        $this->dumpDB($backup_dir . Config::get('database')->name . ".sql");
                        chmod($backup_dir . Config::get('database')->name . ".sql", 0777);

                        ### Архивируем
                        $zipFile = new ZipFile();
                        try {

                            // Выбираем папки для архивации
                            $archive_list = scandir($archive_dir);
                            foreach ($archive_list as $dir_name) {

                                // Пропускаем ненужные файлы. Убираем .php
                                if (!in_array($dir_name, ['resize', 'backup', '.', '..'])) {
                                    if (is_dir($archive_dir . $dir_name)) {
                                        $zipFile->addDirRecursive($archive_dir . $dir_name, $dir_name);
                                    }
                                }
                            }

                            $zipFile->addFile($backup_dir . Config::get('database')->name . ".sql");
                            $zipFile->saveAsFile($file_path);
                        } catch (\PhpZip\Exception\ZipException $e) {
                            trigger_error('Не могу заархивировать ' . $e);
                        } finally {
                            $zipFile->close();
                            unlink($backup_dir . Config::get('database')->name . ".sql");
                            Design::append('service_messages_success', 'created');
                        }
                        break;
                    }

                    // Восстанавливаем Бэкап
                case 'restore': {
                        $name = Request::post('name');
                        $archive_path = $backup_dir . $name;

                        $zipFile = new ZipFile();
                        try {

                            $zipFile->openFile($archive_path);
                            $this->clean_dir($archive_dir);
                            $zipFile->extractTo($archive_dir);

                            $this->restore($archive_dir . Config::get('database')->name . ".sql");
                        } catch (\PhpZip\Exception\ZipException $e) {
                            trigger_error('Не могу разархивировать ' . $e);
                        } finally {
                            $zipFile->close();
                            unlink($archive_dir . Config::get('database')->name . ".sql");
                            Design::append('service_messages_success', 'restored');
                        }
                        break;
                    }

                    // Удаляем Бэкап
                case 'delete': {
                        $names = Request::post('check');
                        foreach ($names as $name) {
                            unlink($backup_dir . $name);
                        }
                        break;
                    }
            }
        }

        $backup_files = glob($backup_dir . "*.zip");
        $backups = [];
        if (is_array($backup_files)) {
            foreach ($backup_files as $backup_file) {
                $backup = new \stdClass();
                $backup->name = basename($backup_file);
                $backup->size = filesize($backup_file);
                $backups[] = $backup;
            }
        }

        $backups = array_reverse($backups);
        if (!is_writable($backup_dir)) {
            Design::assign('message_error', 'no_permission');
        }

        Design::assign('backup_dir', $backup_dir);
        Design::assign('backups', $backups);

        return $this->fetchResponse('settings/backup.tpl');
    }


    /**
     * Clean dir
     * @param $path
     */
    private function clean_dir($path)
    {
        $path = rtrim($path, '/') . '/';
        $handle = opendir($path);

        while (false !== ($file = readdir($handle))) {
            if (!in_array($file, ['.', '..', 'backup'])) {
                $fullpath = $path . $file;
                if (is_dir($fullpath)) {
                    $this->clean_dir($fullpath);
                    rmdir($fullpath);
                } else {
                    unlink($fullpath);
                }
            }
        }

        closedir($handle);
    }


    /**
     * Создаем бэкап всех таблиц базы данных.
     * Сохраняем в вайл
     * @param $filename
     */
    private function dumpDB($filename)
    {
        $h = fopen($filename, 'w');

        // Выбираем все таблицы

        $result = DB::select("SHOW FULL TABLES LIKE '__%';");

        foreach ($result as $row) {

            // Имя таблицы — это свойство с динамическим ключом
            $table_name = array_values((array) $row)[0];
            $table_type = array_values((array) $row)[1];

            if ($table_type == 'BASE TABLE') {
                $this->dumpTable($table_name, $h);
            }
        }

        fclose($h);
    }


    /**
     * Dunmp of table
     */
    private function dumpTable($table, $h)
    {

        $rows = DB::select("SELECT * FROM `$table`");

        if ($rows) {
            fwrite($h, "/* Data for table $table */\n");
            fwrite($h, "TRUNCATE TABLE `$table`;\n");

            $num_rows = count($rows);

            if ($num_rows > 0) {
                $meta = DB::select("SHOW COLUMNS FROM `$table`");
                $field_type = [];
                $field_name = [];
                foreach ($meta as $m) {
                    $field_type[] = $m->Type;
                    $field_name[] = $m->Field;
                }

                $fields = join('`, `', $field_name);
                fwrite($h, "INSERT INTO `$table` (`$fields`) VALUES\n");
                $index = 0;

                foreach ($rows as $row) {
                    fwrite($h, "(");
                    foreach ($field_name as $i => $name) {
                        $value = $row->$name;
                        if (is_null($value)) {
                            fwrite($h, "null");
                        } else {
                            if (preg_match('/int|double|float|decimal|numeric/i', $field_type[$i])) {
                                fwrite($h, $value);
                            } else {
                                fwrite($h, DB::getPdo()->quote($value));
                            }
                        }
                        if ($i < count($field_name) - 1) {
                            fwrite($h, ",");
                        }
                    }
                    fwrite($h, ")");

                    if ($index < $num_rows - 1) {
                        fwrite($h, ",");
                    } else {
                        fwrite($h, ";");
                    }
                    fwrite($h, "\n");

                    $index++;
                }
            }
        }
        fwrite($h, "\n");
    }


    /**
     * Восстанавливаем БД из файла
     * @param $filename
     */
    private function restore($filename)
    {
        $templine = '';
        $h = fopen($filename, 'r');

        // Loop through each line
        if ($h) {
            while (!feof($h)) {
                $line = fgets($h);

                // Only continue if it's not a comment
                if (substr($line, 0, 2) != '--' && $line != '') {

                    // Add this line to the current segment
                    $templine .= $line;

                    // If it has a semicolon at the end, it's the end of the query
                    if (substr(trim($line), -1, 1) == ';') {

                        // Perform the query
                        try {
                            DB::unprepared($templine);
                        } catch (\Throwable $e) {
                            print('Error performing query <b>' . $templine . '</b>: ' . $e->getMessage() . '<br/><br/>');
                        }

                        // Reset temp variable to empty
                        $templine = '';
                    }
                }
            }
        }
        fclose($h);
    }
}
