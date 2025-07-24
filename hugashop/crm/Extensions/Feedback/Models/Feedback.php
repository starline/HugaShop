<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.3
 *
 */

namespace HugaShop\Extensions\Feedback\Models;

use HugaShop\Extensions\BaseExtensionModel;

final class Feedback extends BaseExtensionModel
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
