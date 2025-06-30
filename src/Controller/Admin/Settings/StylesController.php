<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.2
 *
 * Style
 *
 */

namespace App\Controller\Admin\Settings;

use HugaShop\Models\Config;
use HugaShop\Services\Design;
use HugaShop\Models\Request;
use HugaShop\Models\Settings;
use App\Controller\BaseAdminController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class StylesController extends BaseAdminController
{
    private $styles_dir;

    #[Route('/admin/styles', name: 'StylesAdmin')]
    public function index(): Response
    {

        $this->checkAdminAccess('design');

        $this->styles_dir = Config::get('root_dir') . 'assets/' . Settings::getParam('theme') . '/css/';
        $styles = [];

        // Порядок файлов в меню
        $sort = ['style.css'];

        // Читаем все css-файлы
        if ($handle = opendir($this->styles_dir)) {
            $i = count($sort);
            while (false !== ($file = readdir($handle))) {
                if (is_file($this->styles_dir . $file) && $file[0] != '.'  && pathinfo($file, PATHINFO_EXTENSION) == 'css') {
                    if (($key = array_search($file, $sort)) !== false) {
                        $styles[$key] = $file;
                    } else {
                        $styles[$i++] = $file;
                    }
                }
            }
            closedir($handle);
        }
        ksort($styles);

        // Текущий шаблон
        $style_file = Request::get('file');

        if (!empty($style_file) && pathinfo($style_file, PATHINFO_EXTENSION) != 'css') {
            exit();
        }


        // Если не указан - вспоминаем его из сессии
        if (empty($style_file) && !empty(Request::getSession('last_edited_style'))) {
            $style_file = Request::getSession('last_edited_style');
        }
        // Иначе берем первый файл из списка
        elseif (empty($style_file)) {
            $style_file = reset($styles);
        }

        // Передаем имя шаблона в дизайн
        Design::assign('style_file', $style_file);

        // Если можем прочитать файл - передаем содержимое в дизайн
        if (is_readable($this->styles_dir . $style_file)) {
            $style_content = file_get_contents($this->styles_dir . $style_file);
            Design::assign('style_content', $style_content);
        }

        // Если нет прав на запись - передаем в дизайн предупреждение
        if (!empty($style_file) && !is_writable($this->styles_dir . $style_file) && !is_file($this->styles_dir . '../locked')) {
            Design::assign('message_error', 'permissions');
        } elseif (is_file($this->styles_dir . '../locked')) {
            Design::assign('message_error', 'theme_locked');
        } else {

            // Запоминаем в сессии имя редактируемого шаблона
            Request::setSession('last_edited_style', $style_file);
        }

        Design::assign('current_theme', Settings::getParam('theme'));
        Design::assign('styles', $styles);

        return $this->fetchResponse('settings/styles.tpl');
    }
}
