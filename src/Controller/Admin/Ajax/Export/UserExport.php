<?php

namespace App\Controller\Admin\Ajax\Export;

use HugaShop\Api\User\User;
use HugaShop\Api\Config;
use HugaShop\Api\Request;
use App\Controller\BaseAdminController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

class UserExport extends BaseAdminController
{
    // Названия столбцов соответсвуют названиям в mySQL
    private $columns_names = array(
        'id' =>             'id',
        'name' =>           'name',
        'email' =>          'Email',
        'phone' =>          'phone',
        'group_name' =>     'group_name',
        'discount' =>       'discount',
        'enabled' =>        'enabled',
        'created' =>        'created',
        'last_ip' =>        'last_ip',
        'comment' =>        'comment'
    );

    private $column_delimiter = ';';
    private $users_count = 100;
    private $filename = 'users.csv';


    #[Route('/admin/ajax/users/export')]
    public function index()
    {

        $this->checkAdminAccess('export');

        $export_file_path = Config::get('export_files_dir') . $this->filename;

        // Эксель кушает только 1251
        //setlocale(LC_ALL, 'ru_RU.1251');

        // Страница, которую экспортируем
        $page = Request::get('page');
        if (empty($page) || $page == 1) {
            $page = 1;

            // Если начали сначала - удалим старый файл экспорта
            if (is_writable($export_file_path)) {
                unlink($export_file_path);
            }
        }

        // Открываем файл экспорта на добавление
        $f = fopen($export_file_path, 'ab');

        // Если начали сначала - добавим в первую строку названия колонок
        if ($page == 1) {
            fputcsv($f, $this->columns_names, $this->column_delimiter);
        }

        $filter = array();
        $filter['page'] = $page;
        $filter['limit'] = $this->users_count;

        if (!empty(Request::get('group_id'))) {
            $filter['group_id'] = intval(Request::get('group_id'));
        }

        $filter['sort'] = Request::get('sort');
        $filter['keyword'] = Request::get('keyword', "string");

        // Выбираем пользователей
        foreach (User::getUsers($filter) as $u) {
            $str = array();
            foreach ($this->columns_names as $n => $c) {
                $str[] = $u->$n;
            }

            fputcsv($f, $str, $this->column_delimiter);
        }
        fclose($f);
        $total_users = User::countUsers($filter);

        $res = array('end' => true, 'page' => $page, 'totalpages' => ceil($total_users / $this->users_count));
        if (($this->users_count * $page) < $total_users) {
            $res['end'] = false;
        }

        return new JsonResponse($res);
    }
}
