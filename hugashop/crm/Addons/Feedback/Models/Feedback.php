<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.4
 *
 */

namespace HugaShop\Addons\Feedback\Models;

use HugaShop\Addons\BaseAddonModel;

final class Feedback extends BaseAddonModel
{

    public $timestamps = true;
    protected static $table_fields = [
        'id' =>                 ['type' => 'int',           'extra' => 'AUTO_INCREMENT'],
        'name' =>               ['type' => 'varchar',       'req' => true],
        'ip' =>                 ['type' => 'varchar',       'length' => 20],
        'email' =>              ['type' => 'varchar'],
        'message' =>            ['type' => 'text']
    ];
}
