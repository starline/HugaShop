<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.2
 *
 * Images
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

class ImagesController extends BaseAdminController
{


    #[Route('/admin/images', name: 'ImagesAdmin')]
    public function index(): Response
    {

        $this->checkAdminAccess('design');

        $images_dir = Config::get('templates_dir') . Settings::getParam('theme') . '/assets/images/';
        $images_url = Settings::getParam('theme') . '/assets/images';

        $allowed_extentions = array('png', 'gif', 'jpg', 'jpeg', 'ico');
        $images = [];

        // Сохраняем
        if (Request::method('post') && !is_file($images_dir . '../locked')) {
            $old_names = Request::post('old_name');
            $new_names = Request::post('new_name');
            if (is_array($old_names)) {
                foreach ($old_names as $i => $old_name) {
                    $new_name = $new_names[$i];
                    $new_name = trim(pathinfo($new_name, PATHINFO_FILENAME) . '.' . pathinfo($old_name, PATHINFO_EXTENSION), '.');

                    if (is_writable($images_dir) && is_file($images_dir . $old_name) && !is_file($images_dir . $new_name)) {
                        rename($images_dir . $old_name, $images_dir . $new_name);
                    } elseif (is_file($images_dir . $new_name) && $new_name != $old_name) {
                        $message_error = 'name_exists';
                    }
                }
            }

            $delete_image = trim(Request::post('delete_image'), '.');

            if (!empty($delete_image)) {
                @unlink($images_dir . $delete_image);
            }

            // Загрузка изображений
            if ($images = Request::files('upload_images')) {
                for ($i = 0; $i < count($images['name']); $i++) {
                    $name = trim($images['name'][$i], '.');
                    if (in_array(strtolower(pathinfo($name, PATHINFO_EXTENSION)), $allowed_extentions)) {
                        move_uploaded_file($images['tmp_name'][$i], $images_dir . $name);
                    }
                }
            }

            if (!isset($message_error)) {
                header("Location: " . $_SERVER['REQUEST_URI']);
                exit();
            } else {
                Design::assign('message_error', $message_error);
            }
        }


        // Чтаем все файлы
        if ($handle = opendir($images_dir)) {
            while (false !== ($file = readdir($handle))) {
                if (is_file($images_dir . $file) && $file[0] != '.' && in_array(pathinfo($file, PATHINFO_EXTENSION), $allowed_extentions)) {
                    $image = new \stdClass();
                    $image->name = $file;
                    $image->size = filesize($images_dir . $file);
                    list($image->width, $image->height) = @getimagesize($images_dir . $file);
                    $images[$file] = $image;
                }
            }
            closedir($handle);
            ksort($images);
        }


        // Если нет прав на запись - передаем в дизайн предупреждение
        if (!is_writable($images_dir)) {
            Design::assign('message_error', 'permissions');
        } elseif (is_file($images_dir . '../locked')) {
            Design::assign('message_error', 'theme_locked');
        }

        Design::assign('current_theme', Settings::getParam('theme'));
        Design::assign('images', $images);
        Design::assign('images_dir', $images_dir);
        Design::assign('images_url', $images_url);

        return $this->fetchResponse('settings/images.tpl');
    }
}
