<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.7
 *
 */

namespace HugaShop\Api\Content;

use HugaShop\Api\BaseModel;
use HugaShop\Api\Helper;

class ContentPage extends BaseModel
{
    public static $table_fields = [
        'id' =>                     ['type' => 'int',           'extra' => 'AUTO_INCREMENT'],
        'name' =>                   ['type' => 'varchar',       'required' => 'true'],
        'url' =>                    ['type' => 'varchar',       'required' => 'true'],
        'h1' =>                     ['type' => 'varchar'],
        'meta_title' =>             ['type' => 'varchar'],
        'meta_description' =>       ['type' => 'varchar'],
        'body' =>                   ['type' => 'text'],
        'menu' =>                   ['type' => 'tinyint',       'def' => 0],
        'position' =>               ['type' => 'int',           'def' => 0],
        'visible' =>                ['type' => 'tinyint',       'def' => 0]
    ];

    private static $menu;


    /**
     * Функция возвращает страницу по ее id или url (в зависимости от типа)
     * @param int|string $id = id или url страницы
     * @param array $filter
     */
    public static function getPage(int|string $id, array $filter = [])
    {

        // Check Cache
        foreach (ContentPage::getMenu() as $menu_page) {
            if ($menu_page->id == $id || $menu_page->url == $id) {
                return $menu_page;
            }
        }

        if (is_numeric($id) and is_int(intval($id))) {
            $filter['id'] = $id;
        } else {
            $filter['url'] = $id;
        }

        return ContentPage::getOne($filter);
    }


    /**
     * Get Menu
     * Use Cache
     */
    public static function getMenu()
    {
        if (!empty(self::$menu)) {
            return self::$menu;
        }

        // Cache
        $cache_item = Helper::cache()->getItem(Helper::class_basename(ContentPage::class));

        if (!$cache_item->isHit()) {
            $menu = ContentPage::getList(['menu' => 1, 'visible' => 1], order: 'position');
            Helper::cache()->save($cache_item->set($menu));
        } else {
            $menu = $cache_item->get();
        }

        return self::$menu = $menu;
    }


    public static function updatePage(int|array $id, object|array $entity)
    {
        Helper::cache()->delete(Helper::class_basename(ContentPage::class)); # Cache clean
        return ContentPage::updateOne($id, $entity);
    }


    public static function deletePage(int|array $id)
    {
        Helper::cache()->delete(Helper::class_basename(ContentPage::class)); # Cache clean
        return ContentPage::deleteOne($id);
    }
}
