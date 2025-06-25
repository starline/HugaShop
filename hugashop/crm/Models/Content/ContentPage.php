<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.8
 *
 */

namespace HugaShop\Models\Content;

use HugaShop\Models\Helper;
use HugaShop\Models\BaseModel;

class ContentPage extends BaseModel
{
    protected static $table_fields = [
        'id' =>                     ['type' => 'int',           'extra' => 'AUTO_INCREMENT'],
        'url' =>                    ['type' => 'varchar',                               'required' => 'true'],
        'name' =>                   ['type' => 'varchar',       'trans' => true,        'required' => 'true'],
        'h1' =>                     ['type' => 'varchar',       'trans' => true],
        'meta_title' =>             ['type' => 'varchar',       'trans' => true],
        'meta_description' =>       ['type' => 'varchar',       'trans' => true],
        'body' =>                   ['type' => 'text',          'trans' => true],
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
     * Get Menu. Use Cache
     */
    public static function getMenu()
    {

        if (!empty(self::$menu)) {
            return self::$menu;
        }

        // Cache
        $cache_item = Helper::cache(self::class)->getItem('menu');

        if (!$cache_item->isHit()) {
            $menu = ContentPage::getList(['menu' => 1, 'visible' => 1], order: 'position');
            Helper::cache(self::class)->save($cache_item->set($menu));
        } else {
            $menu = $cache_item->get();
        }

        return self::$menu = $menu;
    }


    /**
     * Create page
     */
    public static function addPage(object|array $page)
    {
        $page = Helper::makeUniqSlug(self::class, $page);
        return static::create($page);
    }


    public static function updatePage(int|array $id, object|array $entity)
    {
        Helper::cache(self::class)->clear(); # Cache clean
        return ContentPage::updateOne($id, $entity);
    }


    public static function deletePage(int|array $id)
    {
        Helper::cache(self::class)->clear(); # Cache clean
        return ContentPage::deleteOne($id);
    }
}
