<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.5
 *
 */

namespace App\Services;

use HugaShop\Models\Image;
use HugaShop\Services\Design;
use HugaShop\Services\Helper;
use HugaShop\Services\Secure;
use HugaShop\Models\User\User;
use HugaShop\Services\Request;
use HugaShop\Services\NotifierFactory;
use HugaShop\Models\Content\ContentComment;


class CommentService
{

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
        if (Secure::checkCSRF()) {

            $comment = new \stdClass();
            $comment->name =        Request::post('comment_name', 'string');
            $comment->text =        Request::post('comment_text', 'string');
            $comment->related_id =  Request::postInt('comment_related_id');
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
                if (empty(User::authUser('id')) && !Helper::checkCaptcha()) {
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
                    $comment = ContentComment::createOne($comment);

                    // Загружаем изображения только для корневых комментариев
                    if (empty($comment->related_id) && ($files = Request::files('comment_images'))) {
                        $tmp_names = $files['tmp_name'] ?? [];
                        $names = $files['name'] ?? [];
                        $limit = 6;
                        foreach ($tmp_names as $i => $tmp) {
                            if ($i >= $limit) {
                                break;
                            }
                            if (!empty($tmp)) {
                                Image::uploadAddImage($tmp, $names[$i] ?? 'image', $comment->id, 'comment');
                            }
                        }
                    }

                    // Отправляем email
                    NotifierFactory::sendToManagers([NotifierFactory::class, 'commentToAdmin'], ['comment_id' => $comment->id]);
                    
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
