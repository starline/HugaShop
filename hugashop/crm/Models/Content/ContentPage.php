<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.9
 *
 */

namespace HugaShop\Models\Content;

use HugaShop\Services\Cache;
use HugaShop\Services\Helper;
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
        $cache_item = Cache::cache(self::class)->getItem('menu');

        if (!$cache_item->isHit()) {
            $menu = ContentPage::getList(['menu' => 1, 'visible' => 1], order: 'position');
            Cache::cache(self::class)->save($cache_item->set($menu));
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
        return self::createOne($page);
    }


    public static function updatePage(int|array $id, object|array $page)
    {
        Cache::cache(self::class)->clear(); # Cache clean

        $page = Helper::makeUniqSlug(self::class, $page); # If the URL exists, change it
        return self::updateOne($id, $page);
    }


    public static function deletePage(int|array $id)
    {
        Cache::cache(self::class)->clear(); # Cache clean
        return self::deleteOne($id);
    }
}
