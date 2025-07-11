<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.4
 *
 */

namespace HugaShop\Extensions\TestScript\Services;

use HugaShop\Services\Config;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\Process;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\PhpExecutableFinder;

final class Composer
{

    public static function composerUpdate()
    {
        $phpFinder = new PhpExecutableFinder();

        if ($phpBin = $phpFinder->find()) { # /usr/local/bin/php

            // Composer Update
            //$process = new Process([$phpBin, 'composer.phar', 'update'], Config::get('root_dir')); # If there is composer.phar file
            $process = new Process(['composer', 'update'], Config::get('root_dir')); # If installed composer globaly
            $process->mustRun(function ($type, $line) {
                $result[] = $line;
            });
            $result[] = 'End composer upadte.';


            // ImportMap Compile
            $process = new Process(['bin/console', 'asset-map:compile'], Config::get('root_dir'));
            $process->mustRun(function ($type, $line) {
                $result[] = $line;
            });
            $result[] = 'End AssetMapper compile.';


            // Finder. Clear Compiled and Cache CRM file
            $finder = new Finder();
            $finder->in([Config::get('compiled_dir'), Config::get('app_cache_dir')]);

            // Clear files
            $files_count = 0;
            foreach ($finder->files() as $clean_file) {
                @unlink($clean_file->getRealPath());
                $files_count++;
            }
            $result[] = 'Clear files: ' . $files_count;

            $filesystem = new Filesystem();
            $filesystem->remove([Config::get('compiled_dir'), Config::get('app_cache_dir')]);

            return 'Clear ../compiled and ../cache/hugashop directories';
        }
    }
}
