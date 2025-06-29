<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.2
 *
 */

namespace HugaShop\Models\Content;

use HugaShop\Models\BaseModel;
use Illuminate\Database\Eloquent\Builder;

class ContentFeedback extends BaseModel
{

    protected static $table_fields = [
        'id' =>                 ['type' => 'int',           'lenght' => 11,     'extra' => 'AUTO_INCREMENT'],
        'name' =>               ['type' => 'varchar',       'req' => true],
        'date' =>               ['type' => 'tinyint',       'def' => 'CURRENT_TIMESTAMP'],
        'ip' =>                 ['type' => 'varchar',       'length' => 20],
        'email' =>              ['type' => 'varchar'],
        'message' =>            ['type' => 'text']
    ];


    /**
     * Get Feadback List
     * @param array $filter
     */
    public static function getFeedbacks(array $filter = array(), $new_on_top = false, $count = false)
    {

        $query = self::query();

        // Фильтр по ключевым словам (поиск по name, email, message)
        if (!empty($filter['keyword'])) {
            $keywords = explode(' ', $filter['keyword']);
            $query->where(function (Builder $q) use ($keywords) {
                foreach ($keywords as $word) {
                    $q->orWhere('name', 'like', '%' . trim($word) . '%')
                        ->orWhere('email', 'like', '%' . trim($word) . '%')
                        ->orWhere('message', 'like', '%' . trim($word) . '%');
                }
            });
        }

        // Подсчёт
        if ($count) {
            return $query->count();
        }

        // Сортировка по id
        $query->orderBy('id', $new_on_top ? 'desc' : 'asc');

        // Лимит и пагинация
        if (!empty($filter['limit']) && $filter['limit'] !== 'all') {
            $limit = max(1, (int)$filter['limit']);
            $page = max(1, (int)($filter['page'] ?? 1));
            $query->skip(($page - 1) * $limit)->take($limit);
        }

        return $query->get();
    }


    /**
     * Count Feadback
     * @param array $filter
     */
    public static function countFeedbacks(array $filter = [])
    {
        return self::getFeedbacks($filter, count: true);
    }


    /**
     * Добавляем Feedback
     * @param $feedback
     */
    public static function addFeedback($feedback)
    {
        $feedback->date = date("Y-m-d H:i:s");
        return ContentFeedback::createOne($feedback);
    }
}
