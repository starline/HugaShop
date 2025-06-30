<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.3
 *
 */

namespace App\Controller\Admin\Settings;

use DateTimeZone;
use HugaShop\Models\Image;
use HugaShop\Models\Config;
use HugaShop\Services\Design;
use HugaShop\Models\Request;
use HugaShop\Models\Settings;
use App\Controller\BaseAdminController;
use HugaShop\Models\Finance\FinanceCategory;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SettingsController extends BaseAdminController
{
    private $allowed_image_extentions = array('png', 'gif', 'jpg', 'jpeg', 'ico');
    
    private $fields = [
        'domain' =>                             ['type' => 'string', 'trim' => true],
        'company_name' =>                       ['type' => 'string', 'trim' => true, 'empty' => false],
        'company_description' =>                ['type' => 'string', 'trim' => true],
        'date_format' =>                        ['type' => 'string'],
        'decimals_point' =>                     ['type' => 'string'],
        'thousands_separator' =>                ['type' => 'string'],
        'products_num' =>                       ['type' => 'string'],
        'rel_products_num' =>                   ['type' => 'int'],
        'products_num_admin' =>                 ['type' => 'int'],
        'max_order_amount' =>                   ['type' => 'int'],
        'units' =>                              ['type' => 'string'],
        'weight_units' =>                       ['type' => 'string'],
        'product_meta_description' =>           ['type' => 'string'],
        'emojis' =>                             ['type' => 'string'],
        'expense_finance_category_id' =>        ['type' => 'int'],
        'income_finance_category_id' =>         ['type' => 'int'],
        'timezone' =>                           ['type' => 'varchar']           # Europe/Moscow Europe/Kiev
    ];


    #[Route('/admin/settings', name: 'SettingsAdmin')]
    public function index(): Response
    {

        $this->checkAdminAccess('settings');


        #### Update
        ###########
        if (!empty($settings = Request::getDataAcces($this->fields))) {

            // Выбираем найстройки из POST
            foreach ($settings as $name => $val) {
                Settings::set($name, $val); # save settings
            }

            // Водяной знак
            $watermark = Request::files('watermark_file', 'tmp_name');

            if (!empty($watermark) && in_array(pathinfo(Request::files('watermark_file', 'name'), PATHINFO_EXTENSION), $this->allowed_image_extentions)) {
                if (@move_uploaded_file($watermark, Config::get('images_watermark_file'))) {
                    Image::clearImageResize();
                } else {
                    Design::assign('message_error', 'watermark_is_not_writable');
                }
            }

            if (Settings::getParam('watermark_offset_x') != Request::post('watermark_offset_x')) {
                Settings::set('watermark_offset_x', Request::post('watermark_offset_x'));
                Image::clearImageResize();
            }

            if (Settings::getParam('watermark_offset_y') != Request::post('watermark_offset_y')) {
                Settings::set('watermark_offset_y', Request::post('watermark_offset_y'));
                Image::clearImageResize();
            }

            if (Settings::getParam('watermark_transparency') != Request::post('watermark_transparency')) {
                Settings::set('watermark_transparency', Request::post('watermark_transparency'));
                Image::clearImageResize();
            }

            if (Settings::getParam('images_sharpen') != Request::post('images_sharpen')) {
                Settings::set('images_sharpen', Request::post('images_sharpen'));
                Image::clearImageResize();
            }

            Design::setFlashMessage('update', true);
            return $this->redirectToRoute('SettingsAdmin');
        }


        // Проверяем доступ к библиотеке Imagick
        if (class_exists('Imagick')) {
            Design::assign('imagick', true);
        }

        // Time Zones
        $time_zones = DateTimeZone::listIdentifiers(DateTimeZone::ALL);
        foreach ($time_zones as $key => &$time_zone) {
            if ($time_zone === 'UTC') {
                unset($time_zones[$key]);
            }
        }

        Design::assign('time_zones', $time_zones);

        // Выбираем финансовые категории
        Design::assign('income_finance_categories', FinanceCategory::getCategories(1));
        Design::assign('expense_finance_categories', FinanceCategory::getCategories(0));

        return $this->fetchResponse('settings/settings.tpl');
    }
}
