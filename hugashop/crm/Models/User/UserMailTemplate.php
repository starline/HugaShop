<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.2
 *
 */

namespace HugaShop\Models\User;

use HugaShop\Models\BaseModel;

class UserMailTemplate extends BaseModel
{

    protected static $table_fields = [
        'id' =>             ['type' => 'int',           'extra' => 'AUTO_INCREMENT'],
        'name' =>           ['type' => 'varchar',       'req' => true],
        'content' =>        ['type' => 'text'],
        'type' =>           ['type' => 'varchar'],
        'settings' =>       ['type' => 'text'],
        'create_date' =>    ['type' => 'datetime',      'def' => 'CURRENT_TIMESTAMP']
    ];

    public static $mail_types = [
        'sms' => 'phone',
        'email' => 'email',
        'telegram' => 'chat_id'
    ];
}
