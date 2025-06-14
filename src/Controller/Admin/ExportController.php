<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.7
 *
 */

namespace App\Controller\Admin;

use HugaShop\Api\Config;
use HugaShop\Api\Design;
use App\Controller\BaseAdminController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ExportController extends BaseAdminController
{

    #[Route('/admin/{entity}/export')]
    public function index(string $entity): Response
    {

        $this->checkAdminAccess('export');

        $export_files_url = Config::get('root_url') . '/files/exports/';

        switch ($entity) {
            case 'users':
                $entity_name = 'покупателей';
                break;
            case 'orders':
                $entity_name = 'заказы';
                break;
            case 'products':
                $entity_name = 'товары';
                break;
            case 'product_orders':
                $entity_name = 'Заказы товары';
                break;
            default:
                $entity_name = '';
                break;
        }

        Design::assign('export_files_dir', Config::get('export_files_dir'));
        Design::assign('filter_arr', "{" . $this->implodeForJS($_GET, ',',  ':') . "}");
        Design::assign('entity_name', $entity_name);
        Design::assign('export_file_url', $export_files_url . $entity . '.csv');
        Design::assign('entity', $entity);


        if (!is_writable(Config::get('export_files_dir'))) {
            Design::assign('message_error', 'no_permission');
        }

        return $this->fetchResponse('export_entity.tpl');
    }


    /**
     *  For Symfony use @link https://symfony.com/doc/current/serializer.html
     */
    public function implodeForJS(array $array, string $glue = ',', string $symbol = '=')
    {
        if (empty($array)) {
            return '';
        }

        return implode(
            $glue,
            array_map(
                function ($k, $v) use ($symbol) {
                    return $k . $symbol . '"' . $v . '"';
                },
                array_keys($array),
                array_values($array)
            )
        );
    }
}
