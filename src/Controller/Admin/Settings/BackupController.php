<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 3.7
 *
 * BackupAdmin
 *
 * Use PhpZip lib
 * @link https://github.com/Ne-Lexa/php-zip
 *
 */

namespace App\Controller\Admin\Settings;

use PhpZip\ZipFile;
use HugaShop\Api\Config;
use HugaShop\Api\Design;
use HugaShop\Api\Request;
use HugaShop\Api\Database;
use HugaShop\Api\Settings;
use App\Controller\BaseAdminController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class BackupController extends BaseAdminController
{

    #[Route('/admin/backup', name: 'BackupAdmin')]
    public function index(): Response
    {

        $this->checkAdminAccess('backup');

        set_time_limit(600);

        $backup_dir = Config::get('root_dir') . 'public/files/backup/';
        $archive_dir = Config::get('root_dir') . 'public/files/';

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
                        Database::dump($backup_dir . Config::get('database')->name . ".sql");
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

                            Database::restore($archive_dir . Config::get('database')->name . ".sql");

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
}
