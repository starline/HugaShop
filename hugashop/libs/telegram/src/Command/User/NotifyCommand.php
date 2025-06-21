<?php

/**
 * Notify command
 *
 * @author Andi Huga
 * @version 2.2
 *
 */

namespace Longman\TelegramBot\Commands\UserCommands;

use Spatie\Emoji\Emoji;
use HugaShop\Models\User\User;
use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Entities\ServerResponse;

class NotifyCommand extends UserCommand
{
    /**
     * @var string
     */
    protected $name = 'notify';

    /**
     * @var string
     */
    protected $description = 'Получать увидомления';

    /**
     * @var string
     */
    protected $usage = '/notify';

    /**
     * @var bool
     */
    protected $private_only = true;

    /**
     * @return mixed
     */
    public function execute(): ServerResponse
    {

        $message =              $this->getMessage();
        $message_id =           $message->getMessageId();
        $command_str =          trim($message->getText(true));
        $chat_id =              $message->getChat()->getId();
        $reply_to_message =     $message->getReplyToMessage();
        $user_name =            $message->getChat()->getUsername();

        $text =  Emoji::upsideDownFace() . ' Укажите токен в команде: /notify ...';

        if (!empty($command_str)) {

            $user = User::getUser(['token' =>  $command_str]);

            if (!empty($user->id)) {

                // Save chat_id
                User::updateUser($user->id, ['te_chat_id' =>  $chat_id, 'te_name' => $user_name]);
                $text = Emoji::upsideDownFace() . ' ' . $user->name . ', Вы успешно подписались на оповещения';
            }
        }

        return $this->replyToChat($text);
    }
}
