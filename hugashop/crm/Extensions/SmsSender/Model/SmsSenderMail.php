<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.4
 *
 */

namespace HugaShop\Extensions\SmsSender\Model;

use HugaShop\Api\User\UserMailing;
use HugaShop\Extensions\BaseExtensionModel;

class SmsSenderMail extends BaseExtensionModel
{

    public static $table_fields = [
        'id' =>                 ['type' => 'int',           'extra' => 'AUTO_INCREMENT'],
        'sender_id' =>          ['type' => 'int'],
        'mail_id' =>            ['type' => 'int']
    ];


    /**
     * Список рассылок
     */
    public static function getMailingList(int $sender_id, array $filter = [])
    {
        $mail_ids_list = self::getList(['sender_id' => $sender_id]);

        $mail_ids = [];
        foreach ($mail_ids_list as $rel) {
            $mail_ids[] = $rel->mail_id;
        }

        $filter['id'] = $mail_ids;
        if (!empty($filter['id'])) {
            return UserMailing::getList($filter, order: ['id', 'DESC'], join: ['user', 'notifier']);
        }
        return [];
    }


    /**
     * Список рассылок
     */
    public static function getMailingCount(int $sender_id, array $filter = [])
    {
        $mail_ids_list = self::getList(['sender_id' => $sender_id]);

        $mail_ids = [];
        foreach ($mail_ids_list as $rel) {
            $mail_ids[] = $rel->mail_id;
        }

        $filter['id'] = $mail_ids;
        if (!empty($filter['id'])) {
            return UserMailing::getCount($filter);
        }
        return 0;
    }
}
