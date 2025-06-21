<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.5
 *
 */

namespace HugaShop\Api\Content;

use HugaShop\Api\Design;
use HugaShop\Api\Helper;
use HugaShop\Api\Request;
use HugaShop\Api\BaseModel;
use HugaShop\Api\Product\Product;
use HugaShop\Api\User\User;
use HugaShop\Api\User\UserNotifier;
use Illuminate\Database\Eloquent\Builder;

class ContentComment extends BaseModel
{
    protected static $table_fields = [
        'id' =>                 ['type' => 'int',           'extra' => 'AUTO_INCREMENT'],
        'name' =>               ['type' => 'varchar',       'req' => true],
        'text' =>               ['type' => 'text',          'req' => true],
        'date' =>               ['type' => 'datetime',      'def' => 'CURRENT_TIMESTAMP'],
        'ip' =>                 ['type' => 'varchar',       'access' => false],
        'entity_id' =>          ['type' => 'int'],
        'entity_type' =>        ['type' => 'varchar'],
        'rating' =>             ['type' => 'tinyint',       'def' => 5],
        'related_id' =>         ['type' => 'int',           'access' => false],
        'approved' =>           ['type' => 'tinyint',       'def' => 0],
        'user_id' =>            ['type' => 'int']
    ];

    public static $table_keys = [
        'related_id' => ['related_id'],
        'entity' => ['entity_type', 'entity_id']
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
        return self::where('entity_id', $entity_id)
            ->where('entity_type', $entity_class)
            ->delete();
    }


    /**
     * Handle Comments
     * @param int $entity_id
     * @param string $type blog|product
     */
    public static function handleComments(int $entity_id, string $entity_class)
    {

        // Автозаполнение имени для формы комментария
        if (!empty(User::authUser('name'))) {
            Design::assign('comment_name', User::authUser('name'));
        }

        // Принимаем комментарий
        if (Request::checkCSRF()) {

            $comment = new \stdClass();
            $comment->name =        Request::post('comment_name', 'string');
            $comment->text =        Request::post('comment_text', 'string');
            $comment->related_id =  Request::post('comment_related_id', 'int');
            $check_bot_email =      Request::post('comment_email', 'string');

            $comment->text = strip_tags($comment->text);

            // Передадим комментарий обратно в шаблон - при ошибке нужно будет заполнить форму
            Design::assign('comment_text', $comment->text);
            Design::assign('comment_name', $comment->name);

            // Проверяем заполнение формы
            if (!empty($check_bot_email)) {
                Design::append('form_invalid', 'email');
            }
            if (empty($comment->name)) {
                Design::append('form_invalid', 'name');
            }
            if (empty($comment->text)) {
                Design::append('form_invalid', 'text');
            }

            if (!empty($comment->name) and !empty($comment->text) and empty($check_bot_email)) {

                // Chack Captcha
                if (!Helper::checkCaptcha()) {
                    Design::assign('error', 'captcha');
                } else {

                    // Создаем комментарий
                    $comment->entity_id         = $entity_id;
                    $comment->entity_type       = $entity_class;
                    $comment->ip                = $_SERVER['REMOTE_ADDR'];
                    $comment->approved          = 0;

                    if (!empty(User::authUser('id'))) {
                        $comment->user_id = User::authUser('id');
                    }

                    // Если были одобренные комментарии от текущего ip, одобряем сразу
                    if (ContentComment::checkApprovedByIp($comment->ip) || !empty($comment->user_id)) {

                        // Есть ли ссылка в тексте (http www)
                        $have_url = preg_match("/.*(www|http|\.com).*/i", $comment->text);
                        if (empty($have_url)) {
                            $comment->approved = 1;
                        }
                    }

                    // Добавляем комментарий в базу
                    $comment = ContentComment::create($comment);

                    // Отправляем email
                    UserNotifier::sendNotifierToManager('commentToAdmin', message_params: ['comment_id' => $comment->id]);
                    Request::makeRedirect($_SERVER['REQUEST_URI'] . '#comment_' . $comment->id);
                }
            }
        }

        // Комментарии к посту
        $comments = ContentComment::getComments([
            'entity_type' => $entity_class,
            'entity_id' => $entity_id,
            'approved' => 1,
            'ip' => $_SERVER['REMOTE_ADDR'],
            'answer' => true,
            'sort' => 'ASC'
        ]);

        $comments_total = new \stdClass();
        $comments_total->count = ContentComment::getCommentsCount([
            'entity_type' => $entity_class,
            'entity_id' => $entity_id,
            'approved' => 1
        ]);

        Design::assign('comments', $comments);
        Design::assign('comments_total', $comments_total);

        return $comments;
    }
}
