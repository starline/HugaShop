<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 3.1
 *
 */

namespace HugaShop\Models\Product;

use HugaShop\Models\Image;
use HugaShop\Models\SeoFaqs;
use HugaShop\Services\Cache;
use HugaShop\Services\Helper;
use HugaShop\Models\BaseModel;
use HugaShop\Models\SeoKeywords;
use HugaShop\Models\Product\Product;

class ProductCategory extends BaseModel
{

    protected static $table_fields = [
        'id' =>                 ['type' => 'int'],
        'parent_id' =>          ['type' => 'int'],
        'url' =>                ['type' => 'varchar'],
        'name' =>               ['type' => 'varchar',       'trans' => true,    'req' => true],
        'meta_title' =>         ['type' => 'varchar',       'trans' => true],
        'meta_description' =>   ['type' => 'varchar',       'trans' => true],
        'h1' =>                 ['type' => 'varchar',       'trans' => true],
        'annotation' =>         ['type' => 'text',          'trans' => true],
        'description' =>        ['type' => 'text',          'trans' => true],
        'main' =>               ['type' => 'tinyint',   'def' => 0],
        'visible' =>            ['type' => 'tinyint',   'def' => 0],
        'position' =>           ['type' => 'int',       'def' => 0]
    ];


    private static $all_categories;  # Список указателей на категории в дереве категорий (ключ = id категории)
    private static $categories_tree; # Дерево категорий

    public function images()
    {
        return $this->hasMany(Image::class, 'entity_id')
            ->where('entity_name', 'category')
            ->orderBy('position');
    }

    /**
     * Инициализация категорий, после которой категории будем выбирать из локальной переменной
     */
    private static function initCategories()
    {
        $cache_item = Cache::cache(self::class)->getItem('categories');

        if (!$cache_item->isHit()) {

            // Дерево категорий
            $tree = new \stdClass();
            $tree->subcategories = [];

            // Указатели на узлы дерева
            $pointers = [];
            $pointers[0] = &$tree;
            $pointers[0]->path = [];
            $pointers[0]->level = 0;

            // Выбираем картинки категорий
            $categories = ProductCategory::with(['images']) # Загружаем связанную картинку
                ->orderBy('parent_id')
                ->orderBy('position')
                ->get()->toArray();

            $finish = false;

            // Не кончаем, пока не кончатся категории, или пока ниодну из оставшихся некуда приткнуть
            while (!empty($categories) && !$finish) {
                $flag = false;

                // Проходим все выбранные категории
                foreach ($categories as $k => $category) {

                    $category = (object) $category; # Потому что это Collection 

                    if (isset($pointers[$category->parent_id])) {

                        // В дерево категорий (через указатель) добавляем текущую категорию
                        $pointers[$category->id] = $pointers[$category->parent_id]->subcategories[] = $category;

                        // Путь к текущей категории
                        $curr = $pointers[$category->id];
                        $pointers[$category->id]->path = array_merge((array)$pointers[$category->parent_id]->path, array($curr));

                        // Уровень вложенности категории
                        $pointers[$category->id]->level = 1 + $pointers[$category->parent_id]->level;

                        // Убираем использованную категорию из массива категорий
                        unset($categories[$k]);
                        $flag = true;
                    }
                }

                if (!$flag) {
                    $finish = true;
                }
            }

            // Для каждой категории id всех ее деток узнаем
            $ids = array_reverse(array_keys($pointers));
            foreach ($ids as $id) {
                if ($id > 0) {
                    $pointers[$id]->children[] = $id;

                    if (isset($pointers[$pointers[$id]->parent_id]->children)) {
                        $pointers[$pointers[$id]->parent_id]->children = array_merge($pointers[$id]->children, $pointers[$pointers[$id]->parent_id]->children);
                    } else {
                        $pointers[$pointers[$id]->parent_id]->children = $pointers[$id]->children;
                    }
                }
            }

            unset($pointers[0]);

            $result_cache['categories_tree'] = $tree->subcategories;
            $result_cache['all_categories'] = $pointers;

            Cache::cache(self::class)->save($cache_item->set($result_cache));
        }

        $categories_cache = $cache_item->get();

        self::$categories_tree  = $categories_cache['categories_tree'];
        self::$all_categories   = $categories_cache['all_categories'];
    }


    /**
     * Функция возвращает массив категорий
     * @param array $filter
     */
    public static function getCategories(array $filter = [])
    {
        if (!isset(self::$categories_tree)) {
            self::initCategories();
        }

        if (!empty($filter['product_id'])) {
            $categories_ids = Product::getList(filter: ['id' =>  (array)$filter['product_id']], order: 'position', select: 'id');

            $result = [];
            foreach ($categories_ids as $id) {
                if (isset(self::$all_categories[$id])) {
                    $result[$id] = self::$all_categories[$id];
                }
            }
            return $result;
        }

        // Выбираем категории для показа на главной.
        // filter: level|main
        if (!empty($filter['main'])) {

            $result = [];
            foreach (self::$all_categories as $category) {
                $accept = null;
                foreach ($filter as $param => $value) {
                    if (isset($category->$param) and $category->$param == $value and $accept !== false) {
                        $accept = true;
                    } else {
                        $accept = false;
                        break;
                    }
                }

                if ($accept === true) {
                    $result[] = ProductCategory::getCategoryById($category->id);
                }
            }
            return $result;
        }

        return self::$all_categories;
    }


    /**
     * Функция возвращает id категорий для заданного товара
     * @param $product_id
     */
    public static function getProductCategories($product_id)
    {
        return Product::getList(filter: ['id' => (array)$product_id], order: 'position');
    }


    /**
     * Функция возвращает дерево категорий
     * @param array $filter
     */
    public static function getCategoriesTree(array $filter = [])
    {

        if (!isset(self::$categories_tree)) {
            self::initCategories();
        }

        $categories_tree = self::$categories_tree; # array NOT linking

        if (!empty($filter)) {
            self::arrayFilter($categories_tree, $filter);
        }

        return $categories_tree;
    }


    /**
     * Filtering array
     * @param array $array
     * @param array $filter
     */
    private static function arrayFilter(array &$array, array $filter = [])
    {
        foreach ($array as $key => &$value) {
            foreach ($filter as $param => $param_value) {
                if ($value->$param != $param_value) {
                    unset($array[$key]);
                }
            }

            // Check Subcategories
            if (!empty($value->subcategories)) {
                self::arrayFilter($value->subcategories, $filter);
            }
        }
    }


    /**
     * Функция возвращает заданную категорию
     * @param int|string $id
     */
    public static function getCategory(int|string $id)
    {
        if (!isset(self::$categories_tree)) {
            self::initCategories();
        }

        // Выбираем по ID (Integer)
        if (is_int($id) && array_key_exists(intval($id), self::$all_categories)) {
            return self::$all_categories[$id];
        }

        // Выбираем по URL (String)
        if (is_string($id)) {
            foreach (self::$all_categories as $category) {
                if ($category->url == $id) {
                    return self::$all_categories[$category->id];
                }
            }
        }

        return null;
    }


    /**
     * Get Category by ID
     * @param int $id
     */
    public static function getCategoryById(int $id)
    {
        return self::getCategory($id);
    }


    /**
     * Get category by URL
     * @param string $url
     */
    public static function getCategoryByURL(string $url)
    {
        return self::getCategory($url);
    }


    /**
     * Добавление категории
     * @param $category
     */
    public static function addCategory($category)
    {
        $category = Helper::makeUniqSlug(self::class, $category);
        $category = self::createOne($category);


        Cache::cache(self::class)->clear(); # Cache clean
        self::initCategories();
        return $category;
    }


    /**
     * Изменение категории
     * @param int $id
     * @param $category
     */
    public static function updateCategory(int $id, $category)
    {
        $category = Helper::makeUniqSlug(self::class, $category);
        $result = self::updateOne($id, $category);

        Cache::cache(self::class)->clear(); # Cache clean
        self::initCategories();
        return $result;
    }


    /**
     * Удаление категории
     * @param array $ids
     */
    public static function deleteCategory(array $ids)
    {
        $ids = (array)$ids;
        foreach ($ids as $id) {

            $category = self::getCategoryById($id);

            if (!empty($category->children)) { # Array with current category id and children id

                foreach ($category->children as $cat_id) {

                    // Удаляем основные изображения
                    $images = Image::getImages($cat_id, 'category');
                    foreach ($images as $image) {
                        Image::deleteImage($image->id);
                    }

                    // Удаление синонимов
                    ProductCategorySynonym::deleteCategorySynonyms($cat_id);

                    // Удаление seo_keywords
                    SeoKeywords::deleteKeywords($cat_id, "category");

                    // Удаление seo_faqs
                    SeoFaqs::deleteFAQs($cat_id, "category");
                }

                // Удаление категорий
                if (self::deleteOne((array)$category->children)) {
                    Product::whereIn('category_id', (array)$category->children)->update(['category_id' => NULL]);
                } else {
                    return false;
                }
            }
        }

        Cache::cache(self::class)->clear(); # Cache clean
        self::initCategories();
        return true;
    }
}
