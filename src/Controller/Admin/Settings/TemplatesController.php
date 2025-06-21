<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.3
 *
 * Templates
 *
 */

namespace App\Controller\Admin\Settings;

use HugaShop\Models\Config;
use HugaShop\Models\Design;
use HugaShop\Models\Request;
use HugaShop\Models\Settings;
use App\Controller\BaseAdminController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class TemplatesController extends BaseAdminController
{

    #[Route('/admin/templates', name: 'TemplatesAdmin')]
    public function index(): Response
    {

        $this->checkAdminAccess('design');

        $teme_templates_dir = Config::get('templates_dir') . Settings::getParam('theme') . '/';
        $templates = [];

        // Порядок файлов в меню
        $sort = array(
            'index.tpl',
            'page.tpl',
            'products.tpl',
            'main.tpl',
            'product.tpl',
            'blog.tpl',
            'post.tpl',
            'cart.tpl',
            'order.tpl',
            'user_login.tpl',
            'user_register.tpl',
            'user_password_remind.tpl',
            'user.tpl',
            'feedback.tpl',
            'pagination.tpl'
        );

        // Читаем все tpl-файлы
        if ($handle = opendir($teme_templates_dir)) {
            $i = count($sort);
            while (false !== ($file = readdir($handle))) {
                if (is_file($teme_templates_dir . $file) && $file[0] != '.'  && pathinfo($file, PATHINFO_EXTENSION) == 'tpl') {
                    if (($key = array_search($file, $sort)) !== false) {
                        $templates[$key] = $file;
                    } else {
                        $templates[$i++] = $file;
                    }
                }
            }
            closedir($handle);
            ksort($templates);
        }

        // Текущий шаблон
        $template_file = Request::get('file');

        if (!empty($template_file) && pathinfo($template_file, PATHINFO_EXTENSION) != 'tpl') {
            exit();
        }


        // Если не указан - вспоминаем его из сессии
        if (empty($template_file) && !empty(Request::getSession('last_edited_template'))) {
            $template_file = Request::getSession('last_edited_template');
        }

        // Иначе берем первый файл из списка
        elseif (empty($template_file)) {
            $template_file = reset($templates);
        }

        // Передаем имя шаблона в дизайн
        Design::assign('template_file', $template_file);

        // Если можем прочитать файл - передаем содержимое в дизайн
        if (is_readable($teme_templates_dir . $template_file)) {
            $template_content = file_get_contents($teme_templates_dir . $template_file);
            Design::assign('template_content', $template_content);
        }

        // Если нет прав на запись - передаем в дизайн предупреждение
        if (!empty($template_file) && !is_writable($teme_templates_dir . $template_file) && !is_file($teme_templates_dir . '../locked')) {
            Design::assign('message_error', 'permissions');
        } elseif (is_file($teme_templates_dir . '../locked')) {
            Design::assign('message_error', 'theme_locked');
        } else {

            // Запоминаем в сессии имя редактируемого шаблона
            Request::setSession('last_edited_template', $template_file);
        }

        Design::assign('current_theme', Settings::getParam('theme'));
        Design::assign('templates', $templates);

        return $this->fetchResponse('settings/templates.tpl');
    }
}
