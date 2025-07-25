<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.6
 *
 * Список рассылки сообщений
 * Запускается по cron каждые
 *
 */

namespace HugaShop\Models\User;

use HugaShop\Services\Config;
use HugaShop\Services\Helper;
use HugaShop\Models\BaseModel;
use HugaShop\Services\DesignTwig;
use HugaShop\Services\NotifierFactory;

class UserMailing extends BaseModel
{

    protected static $table_fields = [
        'id' =>                     ['type' => 'int',           'extra' => 'AUTO_INCREMENT'],
        'user_id' =>                ['type' => 'int'],
        'notifier_id' =>            ['type' => 'int',           'req' => true],
        'type' =>                   ['type' => 'varchar'],
        'template_id' =>            ['type' => 'int'],
        'contact' =>                ['type' => 'varchar',       'req' => true],
        'token' =>                  ['type' => 'varchar'],
        'message' =>                ['type' => 'text'],
        'settings' =>               ['type' => 'text'],
        'ip' =>                     ['type' => 'varchar'],
        'count' =>                  ['type' => 'int',           'def' => 0],
        'sent_date' =>              ['type' => 'datetime'],
        'sending_date' =>           ['type' => 'datetime'],
        'frozen' =>                 ['type' => 'tinyint',       'def' => 0],
        'send' =>                   ['type' => 'tinyint',       'def' => 0],
        'create_date' =>            ['type' => 'datetime',      'def' => 'CURRENT_TIMESTAMP']
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function notifier()
    {
        return $this->belongsTo(UserNotifier::class, 'notifier_id');
    }

    public function template()
    {
        return $this->belongsTo(UserMailTemplate::class, 'template_id');
    }

    /**
     * Add Mailing
     */
    public static function addMailing($mailing, bool $send = false)
    {
        $mailing->token = Helper::makeToken(uniqid(), 4);

        // определяем notifier type
        $notifier = UserNotifier::getOne($mailing->notifier_id);
        $mailing->type = $notifier->type;

        // Привязываем пользователя по контакту
        switch ($notifier->type) {
            case 'sms':
                $contact_type = 'phone';
                break;
            case 'email':
                $contact_type = 'email';
                break;
        }

        if (empty($contact_type)) {
            return false;
        }

        // Присваиваем пользователя
        if (!empty($user = User::getUser([$contact_type => $mailing->contact]))) {
            $mailing->user_id = $user->id;
        }

        $mailing = UserMailing::createOne($mailing);

        if (!empty($mailing->id) and $send === true) {
            UserMailing::sendOne($mailing->id);
        }

        return $mailing->id;
    }


    /**
     * Send One
     * @param int $id
     */
    public static function sendOne(int $id)
    {
        if (empty($mailing = UserMailing::getOne($id))) {
            return false;
        }

        if (!empty($notifier->send)) {
            return false;
        }

        $params[UserMailTemplate::$mail_types[$mailing->type]] = $mailing->contact;

        // Рендерим шаблон, вставляю переменные
        $template_params['utm_link'] = UserMailing::makeShortUTMLink($mailing);

        if (!empty($mailing->template_id)) {
            $template   = UserMailTemplate::getOne($mailing->template_id);
            $message    = DesignTwig::renderTemplate($template->content, $template_params);
        } else {
            $message = $mailing->message;
        }

        if (NotifierFactory::send($mailing->notifier_id, $message, $params)) {
            return UserMailing::updateOne($mailing->id, [
                'sent_date' => date('Y-m-d H:i:s'),
                'send' => 1
            ]);
        }

        UserMailing::updateOne($mailing->id, ['frozen' => 1]);
        return false;
    }


    public static function sendList()
    {

        // Выбираем список на отправку на текущее время

        // Выбираем все способы отправки

        // отправляем

        // при успешерй отправки, отмечаем
    }


    /**
     * Make Short Link
     * @param int|object $mail_id
     * @param string $token
     */
    public static function makeShortUTMLink(int|object $mail_id, ?string $token = null)
    {
        if (is_object($mail = $mail_id)) {
            return Config::get('root_url') . '/m' . $mail->id . '/' . $mail->token;
        } else {
            return Config::get('root_url') . '/m' . $mail_id . '/' . $token;
        }
    }
}
