<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.5
 *
 */

namespace HugaShop\Models\Content;

use HugaShop\Models\Image;
use HugaShop\Services\Helper;
use HugaShop\Models\BaseModel;
use Illuminate\Support\Carbon;

class ContentPost extends BaseModel
{

    protected static $table_fields = [
        'id' =>                     ['type' => 'int',           'extra' => 'AUTO_INCREMENT'],
        'url' =>                    ['type' => 'varchar'],
        'name' =>                   ['type' => 'varchar',       'trans' => true, 'required' => 'true'],
        'meta_title' =>             ['type' => 'varchar',       'trans' => true],
        'meta_description' =>       ['type' => 'varchar',       'trans' => true],
        'annotation' =>             ['type' => 'text',          'trans' => true],
        'body' =>                   ['type' => 'mediumtext',    'trans' => true],
        'date' =>                   ['type' => 'datetime',      'def' => 'CURRENT_TIMESTAMP'],
        'visible' =>                ['type' => 'tinyint',       'def' => 0]
    ];

    public function image()
    {
        return $this->hasOne(Image::class, 'entity_id')
            ->where('entity_name', 'post')
            ->orderBy('position');
    }

    public function images()
    {
        return $this->hasMany(Image::class, 'entity_id')
            ->where('entity_name', 'post')
            ->orderBy('position');
    }

    public function comments()
    {
        return $this->morphMany(ContentComment::class, 'entity');
    }


    /**
     * Функция возвращает массив постов, удовлетворяющих фильтру
     * @param $filter
     * @param $count
     */
    public static function getPosts($filter = [], $count = false)
    {

        $query = self::query();

        if (!empty($filter['id'])) {
            $query->whereIn('id', (array)$filter['id']);
        }

        if (isset($filter['visible'])) {
            $query->where('visible', (int)$filter['visible']);
        }

        if (!empty($filter['keyword'])) {
            $keywords = explode(' ', $filter['keyword']);
            foreach ($keywords as $word) {
                $query->where(function ($q) use ($word) {
                    $q->where('name', 'LIKE', "%{$word}%")
                        ->orWhere('meta_description', 'LIKE', "%{$word}%");
                });
            }
        }

        if ($count) {
            return $query->count();
        }

        $query->orderByRaw(
            !empty($filter['random']) && $filter['random'] == 1
                ? 'RAND()'
                : 'date DESC'
        );

        if (!empty($filter['limit']) && $filter['limit'] !== 'all') {
            $limit = max(1, (int)$filter['limit']);
            $page = max(1, (int)($filter['page'] ?? 1));
            $query->limit($limit)->offset(($page - 1) * $limit);
        }

        return $query->get();
    }


    /**
     * Функция вычисляет количество постов, удовлетворяющих фильтру
     * @param $filter
     */
    public static function countPosts($filter = []): int
    {
        return ContentPost::getPosts($filter, count: true);
    }


    /**
     * Создание поста
     * @param $post
     */
    public static function addPost(object|array $post)
    {
        if (empty($post->date)) {
            $post->date = Carbon::now();
        }
        $post = Helper::makeUniqSlug(ContentPost::class, $post);
        return parent::createOne($post);
    }


    /**
     * Обновить пост(ы)
     * @param int $id
     * @param $post
     */
    public static function updatePost(int|array $id, $post)
    {
        $post = Helper::makeUniqSlug(ContentPost::class, $post);
        return ContentPost::updateOne($id, $post);
    }


    /**
     * Удалить пост
     * Delete also comments, images
     * @param int $id
     */
    public static function deletePost(int $id)
    {
        if (ContentPost::deleteOne($id)) {

            // Delete comments
            if (ContentComment::deleteEntityComments($id, ContentPost::class)) {

                // Select all post images
                $images = Image::getImages($id, 'post');
                foreach ($images as $i) {
                    Image::deleteImage($i->id); # Delete images
                }
                return true;
            }
        }
        return false;
    }


    /**
     * Следующий пост
     * @param int $id
     */
    public static function getNextPost(int $id)
    {
        $current_post = self::find($id);
        if (!$current_post) return null;

        return self::where(function ($query) use ($current_post, $id) {
            $query->where(function ($q) use ($current_post, $id) {
                $q->where('date', $current_post->date)
                    ->where('id', '>', $id);
            })->orWhere('date', '>', $current_post->date);
        })->where('visible', 1)
            ->orderBy('date')
            ->orderBy('id')
            ->first();
    }


    /**
     * Предыдущий пост
     * @param int $id
     */
    public static function getPrevPost(int $id)
    {
        $current_post = self::find($id);
        if (!$current_post) return null;

        return self::where(function ($query) use ($current_post, $id) {
            $query->where(function ($q) use ($current_post, $id) {
                $q->where('date', $current_post->date)
                    ->where('id', '<', $id);
            })->orWhere('date', '<', $current_post->date);
        })->where('visible', 1)
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->first();
    }
}
