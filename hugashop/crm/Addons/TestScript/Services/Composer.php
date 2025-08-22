<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.8
 *
 */

namespace HugaShop\Addons\TestScript\Services;

use HugaShop\Services\Config;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\Process;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\PhpExecutableFinder;

final class Composer
{

    public static function allUpdate()
    {

        $result = [];

        // Composer Update
        $result = array_merge($result, self::composerUpdate());

        // ImportMap Compile
        $result = array_merge($result, self::assetMapCompile());

        // Clear compiled dir
        $result = array_merge($result, self::clearCompiledCacheDir());


        // Cache prod|dev clean
        if (0) {
            $result[] = "Start cache clean.";
            $cache_process = new Process(['bin/console', 'cache:clear'], Config::get('root_dir'));
            $cache_process->mustRun(function ($type, $line) use (&$result) {
                $result[] = $line;
            });
            $result[] = 'Cache clean is done.';
        }


        $result[] = '... end.';
        return $result;
    }


    /**
     * Composer update
     */
    public static function composerUpdate()
    {
        // Composer Update
        $result[] = 'Start Composer update.';

        // Check if Composer install globaly
        $process = new Process(['composer', '--version'], Config::get('root_dir')); // на Windows: ['where', 'composer']
        $process->run();

        if ($process->isSuccessful()) {
            $result[] = "Composer found globaly:" . trim($process->getOutput());
            $composer_process = new Process(['composer', 'update'], Config::get('root_dir'));
        } else {
            $result[] = "Composer not found globaly! " .  $process->getErrorOutput();
            $result[] = "Looking for bin/php";

            $phpFinder = new PhpExecutableFinder();
            if ($phpBin = $phpFinder->find()) {  # /usr/local/bin/php
                $result[] = "Try composer.phar";
                $composer_process = new Process([$phpBin, 'composer.phar', 'update'], Config::get('root_dir')); # If there is composer.phar file
            } else {
                $result[] = "Not found bin/php";
            }
        }

        $composer_process->mustRun(function ($type, $line) use (&$result) {
            $result[] = $line;
        });

        if ($composer_process->isSuccessful()) {
            $result[] = 'Composer is upadated.';
        } else {
            $result[] = 'Can not update Composer. ' . $composer_process->getErrorOutput();
        }

        return $result;
    }


    /**
     * ImpoertMap compile
     */
    public static function assetMapCompile()
    {
        $result[] = 'Start AssetMapper compile.';

        $process = new Process(['bin/console', 'asset-map:compile'], Config::get('root_dir'));
        $process->mustRun(function ($type, $line) use (&$result) {
            $result[] = $line;
        });

        if ($process->isSuccessful()) {
            $result[] = 'AssetMapper is compiled.';
        } else {
            $result[] = 'AssetMapper is wrang.';
        }

        return $result;
    }


    /**
     * Clear Compiled cache dir
     */
    public static function clearCompiledCacheDir()
    {
        $result[] = 'Start Compiled dir clean';

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

        $result[] = 'Clear ../compiled and ../cache/hugashop directories';

        return $result;
    }
}
