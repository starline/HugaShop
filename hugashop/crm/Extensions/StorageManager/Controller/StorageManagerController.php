<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.0
 */

namespace HugaShop\Extensions\StorageManager\Controller;

use stdClass;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Routing\Attribute\Route;
use App\Controller\BaseAdminController;
use HugaShop\Extensions\BaseExtensionTrait;
use HugaShop\Services\Config;
use HugaShop\Services\Design;
use HugaShop\Services\Request;

final class StorageManagerController extends BaseAdminController
{
    use BaseExtensionTrait;

    /**
     * Управление файлами хранилища
     */
    #[Route('/StorageManager', name: 'ExtStorageManager', priority: 20)]
    public function index()
    {
        $dirs = [
            'resize' => [
                'path'  => Config::get('images_resized_dir'),
                'clear' => true,
            ],
            'cache' => [
                'path'  => Config::get('cache_dir'),
                // have never clear /var/cache folder with this tool
                'clear' => false,
            ],
            'originals' => [
                'path'  => Config::get('images_originals_dir'),
                'clear' => false,
            ],
            'backup' => [
                'path'  => Config::get('backup_dir'),
                'clear' => false,
            ],
        ];

        if (Request::checkCSRF()) {
            foreach ($dirs as $dirName => $dirParams) {
                if ($dirParams['clear'] === true && Request::post($dirName, 'string')) {
                    if (!is_dir($dirParams['path'])) {
                        continue; // nothing to clean
                    }

                    $finder = new Finder();
                    $finder->in($dirParams['path']);

                    // Clean files
                    foreach ($finder->files() as $file) {
                        @unlink($file->getRealPath());
                    }

                    // Clean dirs. Just parent directory
                    foreach ($finder->directories()->depth('== 0') as $dir) {
                        @rmdir($dir->getRealPath());
                    }

                    break;
                }
            }
        }

        $storages = [];
        $total = new stdClass();
        $total->size = 0;
        $total->files = 0;

        foreach ($dirs as $dirName => $dirParams) {
            $curDir = new stdClass();
            $curDir->size = 0;
            $curDir->files = 0;
            $curDir->clear = $dirParams['clear'];

            if (is_dir($dirParams['path'])) {
                $files = (new Finder())->files()->in($dirParams['path']);
                foreach ($files as $file) {
                    $curDir->size += filesize($file->getRealPath());
                    $curDir->files++;
                }
            }

            $storages[$dirName] = $curDir;

            $total->files += $curDir->files;
            $total->size += $curDir->size;
        }

        Design::assign('storages', $storages);
        Design::assign('total', $total);

        return $this->fetchExtResponse('index.tpl');
    }
}
