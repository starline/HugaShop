<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.3
 *
 * Theme
 *
 */

namespace App\Controller\Admin\Settings;

use HugaShop\Services\Config;
use HugaShop\Services\Design;
use HugaShop\Services\Request;
use HugaShop\Models\Settings;
use App\Controller\BaseAdminController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ThemeController extends BaseAdminController
{
    private $themes_dir;

    #[Route('/admin/theme', name: 'ThemeAdmin')]
    public function index(): Response
    {

        $this->checkAdminAccess('design');

        $this->themes_dir = Config::get('root_dir') . 'templates/';
        $message_error = '';

        if (Request::checkCSRF()) {

            // TODO: Необзхрдимо изменять имя темы в папке public/templates

            $this->dir_delete(Config::get('compiled_dir'), false);

            $old_names = Request::post('old_name');
            $new_names = Request::post('new_name');

            if (is_array($old_names)) {
                foreach ($old_names as $i => $old_name) {
                    $new_name = preg_replace("/[^a-zA-Z0-9\-\_]/", "", $new_names[$i]);

                    if (is_writable($this->themes_dir) && is_dir($this->themes_dir . $old_name) && !is_file($this->themes_dir . $new_name) && !is_dir($this->themes_dir . $new_name)) {
                        rename($this->themes_dir . $old_name, $this->themes_dir . $new_name);
                        if (Settings::getParam('theme') === $old_name) {
                            Settings::set('theme', $new_name);
                        }
                    } elseif (is_file($this->themes_dir . $new_name) && $new_name != $old_name) {
                        $message_error = 'name_exists';
                    }
                }
            }

            $action_theme  = Request::post('theme');
            switch (Request::post('action')) {

                case 'set_main_theme': {
                        Settings::set('theme', $action_theme);
                        break;
                    }

                case 'clone_theme': {
                        $new_name = Settings::getParam('theme');
                        while (is_dir($this->themes_dir . $new_name) || is_file($this->themes_dir . $new_name)) {
                            if (preg_match('/(.+)_([0-9]+)$/', $new_name, $parts)) {
                                $new_name = $parts[1] . '_' . ($parts[2] + 1);
                            } else {
                                $new_name = $new_name . '_1';
                            }
                        }
                        $this->dir_copy($this->themes_dir . Settings::getParam('theme'), $this->themes_dir . $new_name);
                        @unlink($this->themes_dir . $new_name . '/locked');
                        Settings::set('theme', $new_name);
                        break;
                    }

                case 'delete_theme': {
                        $this->dir_delete($this->themes_dir . $action_theme);
                        if ($action_theme === Settings::getParam('theme')) {
                            $t = reset($this->get_themes());
                            Settings::set('theme', $t->name);
                        }
                        break;
                    }
            }
        }

        $themes = $this->get_themes();

        // Если нет прав на запись - передаем в дизайн предупреждение
        if (!is_writable($this->themes_dir)) {
            $message_error = 'permissions';
        }

        $current_theme = new \stdClass();
        $current_theme->name = Settings::getParam('theme');
        $current_theme->locked = is_file($this->themes_dir . $current_theme->name . '/locked');

        Design::assign('message_error', $message_error);
        Design::assign('current_theme', $current_theme);
        Design::assign('themes', $themes);
        Design::assign('themes_dir', $this->themes_dir);

        return $this->fetchResponse('settings/theme.tpl');
    }


    private function dir_copy($src, $dst)
    {
        if (is_dir($src)) {
            mkdir($dst, 0777);
            $files = scandir($src);
            foreach ($files as $file) {
                if ($file != "." && $file != "..") {
                    $this->dir_copy("$src/$file", "$dst/$file");
                }
            }
        } elseif (file_exists($src)) {
            copy($src, $dst);
        }

        @chmod($dst, 0777);
    }


    private function dir_delete($path, $delete_self = true)
    {
        if (!$dh = @opendir($path)) {
            return;
        }
        while (false !== ($obj = readdir($dh))) {
            if ($obj == '.' || $obj == '..') {
                continue;
            }

            if (!@unlink($path . '/' . $obj)) {
                $this->dir_delete($path . '/' . $obj, true);
            }
        }
        closedir($dh);
        if ($delete_self) {
            @rmdir($path);
        }
        return;
    }

    private function get_themes()
    {
        if ($handle = opendir($this->themes_dir)) {
            while (false !== ($file = readdir($handle))) {

                // Except admin
                if (is_dir($this->themes_dir . '/' . $file) && $file[0] != '.' && $file != 'admin') {
                    $theme = new \stdClass();
                    $theme->name = $file;
                    $theme->locked = is_file($this->themes_dir . $file . '/locked');
                    $themes[] = $theme;
                }
            }
            closedir($handle);
            sort($themes);
        }
        return $themes;
    }
}
