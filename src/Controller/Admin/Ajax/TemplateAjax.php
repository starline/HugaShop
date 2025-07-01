<?php

namespace App\Controller\Admin\Ajax;

use HugaShop\Services\Config;
use HugaShop\Services\Request;
use App\Controller\BaseAdminController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

class TemplateAjax extends BaseAdminController
{
    #[Route('/admin/ajax/template/save_style', name: 'TemplateAjaxAdmin')]
    public function save_style()
    {

        $this->checkAdminAccess('design');

        $content = Request::post('content', 'string');
        $style = Request::post('style', 'string');
        $theme = Request::post('theme', 'string');

        if (pathinfo($style, PATHINFO_EXTENSION) != 'css') {
            exit();
        }

        $file = Config::get('root_dir') . 'template/' . $theme . '/css/' . $style;

        if (is_file($file) && is_writable($file) && !is_file(Config::get('root_dir') . 'template/' . $theme . '/locked')) {
            file_put_contents($file, $content);
            $result = true;
        } else {
            $result = false;
        }

        return new JsonResponse($result);
    }


    #[Route('/admin/ajax/template/save_templates')]
    public function save_templates()
    {

        $this->checkAdminAccess('design');

        $content = Request::post('content', 'string');
        $template = Request::post('template', 'string');
        $theme = Request::post('theme', 'string');

        if (pathinfo($template, PATHINFO_EXTENSION) != 'tpl') {
            exit();
        }

        $file = Config::get('root_dir') . 'template/' . $theme . '/html/' . $template;

        if (is_file($file) && is_writable($file) && !is_file(Config::get('root_dir') . 'template/' . $theme . '/locked')) {
            file_put_contents($file, $content);
            $result = true;
        } else {
            $result = false;
        }

        return new JsonResponse($result);
    }
}
