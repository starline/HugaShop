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

class UserNotifierType extends BaseModel
{
    protected static $table_fields = [
        'id' =>                     ['type' => 'int',           'extra' => 'AUTO_INCREMENT'],
        'user_id' =>                ['type' => 'int',           'req' => true],
        'notifier_id' =>            ['type' => 'int',           'req' => true],
        'type' =>                   ['type' => 'varchar']
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function notifier()
    {
        return $this->belongsTo(UserNotifier::class, 'notifier_id');
    }
}
