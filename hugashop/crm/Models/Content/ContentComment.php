<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 3.2
 *
 */

namespace HugaShop\Models\Content;

use HugaShop\Models\Image;
use HugaShop\Models\BaseModel;
use HugaShop\Models\User\User;
use HugaShop\Models\Product\Product;
use Illuminate\Database\Eloquent\Builder;

class ContentComment extends BaseModel
{

    protected static $table_fields = [
        'id' =>                 ['type' => 'int',           'extra' => 'AUTO_INCREMENT'],
        'name' =>               ['type' => 'varchar',       'req' => true],
        'text' =>               ['type' => 'text',          'req' => true],
        'ip' =>                 ['type' => 'varchar',       'access' => false],
        'entity_id' =>          ['type' => 'int'],
        'entity_type' =>        ['type' => 'varchar'],
        'rating' =>             ['type' => 'tinyint',       'def' => 5],
        'related_id' =>         ['type' => 'int',           'access' => false],
        'approved' =>           ['type' => 'tinyint',       'def' => 0],
        'user_id' =>            ['type' => 'int'],
        'date' =>               ['type' => 'datetime',      'def' => 'CURRENT_TIMESTAMP'],
    ];

    protected static $table_keys = [
        'related_id'    => ['column' => ['related_id'],               'type' => 'index'],
        'entity'        => ['column' => ['entity_type', 'entity_id'], 'type' => 'index']
    ];

    public function entity()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function parent()
    {
        return $this->belongsTo(ContentComment::class, 'related_id');
    }

    public function children()
    {
        return $this->hasMany(ContentComment::class, 'related_id');
    }

    public function images()
    {
        return $this->hasMany(\HugaShop\Models\Image::class, 'entity_id')
            ->where('entity_name', 'comment')
            ->orderBy('position');
    }


    /**
     * Get EntityClass 
     */
    public static function getEntityClass(string $type)
    {
        $entity_types = [
            'blog' => ContentPost::class,
            'product' => Product::class
        ];
        return $entity_types[$type] ?? null;
    }


    /**
     * Возвращает комментарии, удовлетворяющие фильтру
     * @param array $filter
     */
    public static function getComments(array $filter = [], array|string $join = [], $count = false)
    {

        $query = self::query();

        // Фильтр по модерации
        if (isset($filter['approved'])) {
            $query->where(function (Builder $q) use ($filter) {
                $q->where('approved', $filter['approved']);

                if (!empty($filter['entity_id']) && !empty($filter['ip'])) {
                    $q->orWhere('ip', $filter['ip']);
                }
            });
        }

        // Фильтр по IP
        if (!empty($filter['ip']) && empty($filter['entity_id'])) {
            $query->where('ip', $filter['ip']);
        }

        // Фильтр по entity_id
        if (!empty($filter['entity_id'])) {
            $query->where('entity_id', $filter['entity_id']);
        }

        // Фильтр по типу
        if (!empty($filter['entity_type'])) {
            $query->where('entity_type', $filter['entity_type']);
        }

        // Поиск по ключевым словам
        if (!empty($filter['keyword'])) {
            $keywords = explode(' ', $filter['keyword']);
            $query->where(function (Builder $q) use ($keywords) {
                foreach ($keywords as $word) {
                    $q->orWhere('name', 'like', '%' . trim($word) . '%')
                        ->orWhere('text', 'like', '%' . trim($word) . '%');
                }
            });
        }

        // Если нужен count
        if ($count === true) {
            return $query->count();
        }

        // Eager load relationships
        if (!empty($joins)) {
            $query->with(is_string($joins) ? [$joins] : $joins);
        }

        // Сортировка
        $sort = $filter['sort'] ?? 'desc';
        $query->orderBy('id', $sort);

        // Пагинация. Только для всего списка
        if (!empty($filter['limit']) && $filter['limit'] !== 'all') {
            $limit = max(1, (int)$filter['limit']);
            $page = max(1, (int)($filter['page'] ?? 1));
            $query->limit($limit)->offset(($page - 1) * $limit);
        }

        $comments = $query->get();

        // Обработка ответов (answer)
        if (!empty($filter['answer'])) {
            $answers = $comments->filter(fn($c) => $c->related_id > 0);
            $roots = $comments->filter(fn($c) => $c->related_id === null);

            $grouped = $answers->groupBy('related_id');

            foreach ($roots as $comment) {
                $comment->answer = $grouped[$comment->id] ?? collect();
            }

            return $roots->values(); # вернуть только корневые
        }

        return $comments;
    }


    /**
     * Количество комментариев, удовлетворяющих фильтру
     * @param array $filter
     */
    public static function getCommentsCount(array $filter = [])
    {
        return ContentComment::getComments($filter, count: true);
    }


    /**
     * Check approved by IP. Return approved count
     */
    public static function checkApprovedByIp(string $ip): int
    {
        return self::where('ip', $ip)
            ->where('approved', 1)
            ->count();
    }


    /**
     * Delete comments
     */
    public static function deleteEntityComments($entity_id, $entity_class)
    {
        $comment_ids = self::where('entity_id', $entity_id)
            ->where('entity_type', $entity_class)
            ->pluck('id');

        if ($comment_ids->isNotEmpty()) {
            Image::deleteEntityImages($comment_ids->toArray(), 'comment');
        }

        return self::where('entity_id', $entity_id)
            ->where('entity_type', $entity_class)
            ->delete();
    }
}
