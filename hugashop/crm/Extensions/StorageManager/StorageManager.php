<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.7
 *
 */

namespace HugaShop\Extensions\StorageManager;

use HugaShop\Services\Config;
use HugaShop\Services\Design;
use HugaShop\Services\Request;
use Symfony\Component\Finder\Finder;
use HugaShop\Extensions\BaseExtension;

final class StorageManager extends BaseExtension
{
    private $dirs;

    public function __construct()
    {
        parent::__construct();

        $this->dirs = [
            'resize' => [
                'path' => Config::get('images_resized_dir'),
                'clear' => true
            ],
            'cache' => [
                'path' => Config::get('cache_dir'),
                'clear' => false # have never clear /var/cache folder with this tool
            ],
            'originals' => [
                'path' => Config::get('images_originals_dir'),
                'clear' => false
            ],
            'backup' =>  [
                'path' => Config::get('backup_dir'),
                'clear' => false
            ]
        ];
    }


    /**
     * For admin panel use default settings template
     */
    public function index()
    {

        // Обработка действий
        if (Request::checkCSRF()) {

            // Действия с выбранными
            foreach ($this->dirs as $dir_name => $dir_params) {
                if ($dir_params['clear'] === true) {
                    if (!empty(Request::post($dir_name, 'string'))) {

                        if (!is_dir($dir_params['path'])) { # nothing to clean
                            continue;
                        }

                        $finder = new Finder();
                        $finder->in($dir_params['path']);

                        // Clean files
                        $clean_files = $finder->files();
                        foreach ($clean_files as $clean_file) {
                            @unlink($clean_file->getRealPath());
                        }

                        // Clean dirs. Just parent directory
                        $clean_dirs = $finder->directories()->depth('== 0');
                        foreach ($clean_dirs as $clean_dir) {
                            @rmdir($clean_dir->getRealPath());
                        }

                        break;
                    }
                }
            }
        }

        $storages = [];
        $total  = new \stdClass();
        $total->size = 0;
        $total->files = 0;

        // Public folder
        foreach ($this->dirs as $dir_name => $dir_params) {

            $cur_dir = new \stdClass();
            $cur_dir->size = 0;
            $cur_dir->files = 0;
            $cur_dir->clear = $dir_params['clear'];

            if (is_dir($dir_params['path'])) {
                $files = (new Finder())->files()->in($dir_params['path']);
                foreach ($files as $file) {
                    $cur_dir->size += filesize($file->getRealPath());
                    $cur_dir->files++;
                }
            }

            $storages[$dir_name] = $cur_dir;

            // Total
            $total->files += $cur_dir->files;
            $total->size += $cur_dir->size;
        }

        Design::assign('storages', $storages);
        Design::assign('total', $total);

        return $this->getTemplatePath('templates/index.tpl');
    }
}
